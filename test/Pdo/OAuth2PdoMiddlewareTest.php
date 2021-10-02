<?php

declare(strict_types=1);

namespace MezzioTest\Authentication\OAuth2\Pdo;

use DateInterval;
use Exception;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\Stream;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\CodeChallengeVerifiers\S256Verifier;
use League\OAuth2\Server\Grant\AuthCodeGrant;
use League\OAuth2\Server\Grant\ClientCredentialsGrant;
use League\OAuth2\Server\Grant\ImplicitGrant;
use League\OAuth2\Server\Grant\PasswordGrant;
use League\OAuth2\Server\Grant\RefreshTokenGrant;
use League\OAuth2\Server\RequestTypes\AuthorizationRequest;
use Mezzio\Authentication\OAuth2\AuthorizationHandler;
use Mezzio\Authentication\OAuth2\AuthorizationMiddleware;
use Mezzio\Authentication\OAuth2\Entity\UserEntity;
use Mezzio\Authentication\OAuth2\Repository\Pdo\AccessTokenRepository;
use Mezzio\Authentication\OAuth2\Repository\Pdo\AuthCodeRepository;
use Mezzio\Authentication\OAuth2\Repository\Pdo\ClientRepository;
use Mezzio\Authentication\OAuth2\Repository\Pdo\PdoService;
use Mezzio\Authentication\OAuth2\Repository\Pdo\RefreshTokenRepository;
use Mezzio\Authentication\OAuth2\Repository\Pdo\ScopeRepository;
use Mezzio\Authentication\OAuth2\Repository\Pdo\UserRepository;
use Mezzio\Authentication\OAuth2\TokenEndpointHandler;
use PDO;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function assert;
use function base64_encode;
use function bin2hex;
use function explode;
use function file_exists;
use function file_get_contents;
use function hash;
use function http_build_query;
use function json_decode;
use function parse_str;
use function random_bytes;
use function rtrim;
use function sprintf;
use function strtolower;
use function strtr;
use function unlink;

/**
 * Integration test for the authorization flows with PDO
 *
 * @coversNothing
 */
class OAuth2PdoMiddlewareTest extends TestCase
{
    use ProphecyTrait;

    private const DB_FILE        = __DIR__ . '/TestAsset/test_oauth2.sq3';
    private const DB_SCHEMA      = __DIR__ . '/../../data/oauth2.sql';
    private const DB_DATA        = __DIR__ . '/TestAsset/test_data.sql';
    private const PRIVATE_KEY    = __DIR__ . '/../TestAsset/private.key';
    private const ENCRYPTION_KEY = 'T2x2+1OGrElaminasS+01OUmwhOcJiGmE58UD1fllNn6CGcQ=';

    private const CODE_VERIFIER = 'dBjftJeZ4CVP-mB92K27uhbUJU1p1r_wW1gFWFOEjXk';

    /** @var AccessTokenRepository */
    private $accessTokenRepository;

    /** @var AuthCodeRepository */
    private $authCodeRepository;

    /** @var AuthorizationServer */
    private $authServer;

    /** @var ClientRepository */
    private $clientRepository;

    /** @var RequestHandlerInterface|ObjectProphecy */
    private $handler;

    /** @var PdoService */
    private $pdoService;

    /** @var RefreshTokenRepository */
    private $refreshTokenRepository;

    /** @var Response */
    private $response;

    /** @var callable */
    private $responseFactory;

    /** @var ScopeRepository */
    private $scopeRepository;

    /** @var UserRepository */
    private $userRepository;

    public static function setUpBeforeClass(): void
    {
        self::tearDownAfterClass();

        // Generate the OAuth2 database
        $pdo = new PDO('sqlite:' . self::DB_FILE);
        if (false === $pdo->exec(file_get_contents(self::DB_SCHEMA))) {
            throw new Exception(sprintf(
                "The test cannot be executed without the %s db",
                self::DB_SCHEMA
            ));
        }
        // Insert the test values
        if (false === $pdo->exec(file_get_contents(self::DB_DATA))) {
            throw new Exception(sprintf(
                "The test cannot be executed without the values in %s",
                self::DB_DATA
            ));
        }
    }

    public static function tearDownAfterClass(): void
    {
        if (file_exists(self::DB_FILE)) {
            unlink(self::DB_FILE);
        }
    }

    protected function setUp(): void
    {
        $this->response               = new Response();
        $this->pdoService             = new PdoService('sqlite:' . self::DB_FILE);
        $this->clientRepository       = new ClientRepository($this->pdoService);
        $this->accessTokenRepository  = new AccessTokenRepository($this->pdoService);
        $this->scopeRepository        = new ScopeRepository($this->pdoService);
        $this->userRepository         = new UserRepository($this->pdoService);
        $this->refreshTokenRepository = new RefreshTokenRepository($this->pdoService);
        $this->authCodeRepository     = new AuthCodeRepository($this->pdoService);

        $this->authServer = new AuthorizationServer(
            $this->clientRepository,
            $this->accessTokenRepository,
            $this->scopeRepository,
            self::PRIVATE_KEY,
            self::ENCRYPTION_KEY
        );

        $this->handler         = $this->prophesize(RequestHandlerInterface::class);
        $this->responseFactory = function () {
            return $this->response;
        };
    }

    /**
     * Test the Client Credential Grant if client is not confidential
     *
     * @see https://oauth2.thephpleague.com/authorization-server/client-credentials-grant/
     */
    public function testProcessClientCredentialGrantNotConfidential()
    {
        // Enable the client credentials grant on the server
        $this->authServer->enableGrantType(
            new ClientCredentialsGrant(),
            new DateInterval('PT1H') // access tokens will expire after 1 hour
        );

        // Server request
        $params  = [
            'grant_type'    => 'client_credentials',
            'client_id'     => 'client_test_not_confidential',
            'client_secret' => 'test',
            'scope'         => 'test',
        ];
        $request = $this->buildServerRequest(
            'POST',
            '/access_token',
            http_build_query($params),
            $params,
            ['Content-Type' => 'application/x-www-form-urlencoded']
        );

        $handler = new TokenEndpointHandler(
            $this->authServer,
            $this->responseFactory
        );

        $response = $handler->handle($request);

        $this->assertEquals(401, $response->getStatusCode());
    }

    /**
     * Test the Client Credential Grant if client is confidential
     *
     * @see https://oauth2.thephpleague.com/authorization-server/client-credentials-grant/
     */
    public function testProcessClientCredentialGrantConfidential()
    {
        // Enable the client credentials grant on the server
        $this->authServer->enableGrantType(
            new ClientCredentialsGrant(),
            new DateInterval('PT1H') // access tokens will expire after 1 hour
        );

        // Server request
        $params  = [
            'grant_type'    => 'client_credentials',
            'client_id'     => 'client_test',
            'client_secret' => 'test',
            'scope'         => 'test',
        ];
        $request = $this->buildServerRequest(
            'POST',
            '/access_token',
            http_build_query($params),
            $params,
            ['Content-Type' => 'application/x-www-form-urlencoded']
        );

        $handler = new TokenEndpointHandler(
            $this->authServer,
            $this->responseFactory
        );

        $response = $handler->handle($request);

        $this->assertEquals(200, $response->getStatusCode());
        $content = json_decode((string) $response->getBody());
        $this->assertEquals('Bearer', $content->token_type);
        $this->assertIsInt($content->expires_in);
        $this->assertNotEmpty($content->access_token);
    }

    /**
     * Test the Password Grant
     *
     * @see https://oauth2.thephpleague.com/authorization-server/resource-owner-password-credentials-grant/
     */
    public function testProcessPasswordGrant()
    {
        $grant = new PasswordGrant(
            $this->userRepository,
            $this->refreshTokenRepository
        );
        $grant->setRefreshTokenTTL(new DateInterval('P1M')); // expire after 1 month
        // Enable the password grant on the server
        $this->authServer->enableGrantType(
            $grant,
            new DateInterval('PT1H') // access tokens will expire after 1 hour
        );
        // Server request
        $params  = [
            'grant_type'    => 'password',
            'client_id'     => 'client_test',
            'client_secret' => 'test',
            'scope'         => 'test',
            'username'      => 'user_test',
            'password'      => 'test',
        ];
        $request = $this->buildServerRequest(
            'POST',
            '/access_token',
            http_build_query($params),
            $params,
            ['Content-Type' => 'application/x-www-form-urlencoded']
        );

        $handler = new TokenEndpointHandler(
            $this->authServer,
            $this->responseFactory
        );

        $response = $handler->handle($request);

        $this->assertEquals(200, $response->getStatusCode());
        $content = json_decode((string) $response->getBody());
        $this->assertEquals('Bearer', $content->token_type);
        $this->assertIsInt($content->expires_in);
        $this->assertNotEmpty($content->access_token);
        $this->assertNotEmpty($content->refresh_token);
    }

    /**
     * Test the Authorization Code Grant flow (Part One)
     *
     * @see https://oauth2.thephpleague.com/authorization-server/auth-code-grant/
     */
    public function testProcessGetAuthorizationCode(): string
    {
        $grant = new AuthCodeGrant(
            $this->authCodeRepository,
            $this->refreshTokenRepository,
            new DateInterval('PT10M') // authorization codes will expire after 10 minutes
        );
        $grant->setRefreshTokenTTL(new DateInterval('P1M')); // refresh tokens will expire after 1 month

        // Enable the authentication code grant on the server
        $this->authServer->enableGrantType(
            $grant,
            new DateInterval('PT1H') // access tokens will expire after 1 hour
        );
        $state = bin2hex(random_bytes(10)); // CSRF token
        // Server request
        $params = [
            'response_type' => 'code',
            'client_id'     => 'client_test2',
            'redirect_uri'  => '/redirect',
            'scope'         => 'test',
            'state'         => $state,
        ];

        $codeVerifier = new S256Verifier();

        $params['code_challenge_method'] = $codeVerifier->getMethod();
        $params['code_verifier']         = self::CODE_VERIFIER;
        $params['code_challenge']        = strtr(
            rtrim(base64_encode(hash('sha256', self::CODE_VERIFIER, true)), '='),
            '+/',
            '-_'
        );

        $request = $this->buildServerRequest(
            'GET',
            '/auth_code?' . http_build_query($params),
            '',
            [],
            [],
            $params
        );

        // mocks the authorization endpoint pipe
        $authMiddleware  = new AuthorizationMiddleware($this->authServer, $this->responseFactory);
        $authHandler     = new AuthorizationHandler($this->authServer, $this->responseFactory);
        $consumerHandler = $this->buildConsumerAuthMiddleware($authHandler);

        $response = $authMiddleware->process($request, $consumerHandler);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertTrue($response->hasHeader('Location'));
        [$url, $queryString] = explode('?', $response->getHeader('Location')[0]);
        $this->assertEquals($params['redirect_uri'], $url);
        parse_str($queryString, $data);
        $this->assertTrue(isset($data['code']));
        $this->assertTrue(isset($data['state']));
        $this->assertEquals($state, $data['state']);

        return $data['code'];
    }

    /**
     * Test the Authorization Code Grant (Part Two)
     *
     * @see https://oauth2.thephpleague.com/authorization-server/auth-code-grant/
     *
     * @depends testProcessGetAuthorizationCode
     */
    public function testProcessFromAuthorizationCode(string $code): string
    {
        $grant = new AuthCodeGrant(
            $this->authCodeRepository,
            $this->refreshTokenRepository,
            new DateInterval('PT10M') // authorization codes will expire after 10 minutes
        );
        $grant->setRefreshTokenTTL(new DateInterval('P1M')); // refresh tokens will expire after 1 month

        // Enable the authentication code grant on the server
        $this->authServer->enableGrantType(
            $grant,
            new DateInterval('PT1H') // access tokens will expire after 1 hour
        );

        // Server request
        $params = [
            'grant_type'    => 'authorization_code',
            'client_id'     => 'client_test2',
            'client_secret' => 'test',
            'redirect_uri'  => '/redirect',
            'code'          => $code,
            'code_verifier' => self::CODE_VERIFIER,
        ];

        $request = $this->buildServerRequest(
            'POST',
            '/access_token',
            http_build_query($params),
            $params,
            ['Content-Type' => 'application/x-www-form-urlencoded']
        );

        $handler = new TokenEndpointHandler(
            $this->authServer,
            $this->responseFactory
        );

        $response = $handler->handle($request);

        $this->assertEquals(200, $response->getStatusCode());
        $content = json_decode((string) $response->getBody());
        $this->assertEquals('Bearer', $content->token_type);
        $this->assertIsInt($content->expires_in);
        $this->assertNotEmpty($content->access_token);
        $this->assertNotEmpty($content->refresh_token);

        return $content->refresh_token;
    }

    /**
     * Test the Implicit Grant
     *
     * @see https://oauth2.thephpleague.com/authorization-server/implicit-grant/
     */
    public function testProcessImplicitGrant()
    {
        // Enable the implicit grant on the server
        $this->authServer->enableGrantType(
            new ImplicitGrant(new DateInterval('PT1H')),
            new DateInterval('PT1H') // access tokens will expire after 1 hour
        );
        $state = bin2hex(random_bytes(10)); // CSRF token
        // Server request
        $params  = [
            'response_type' => 'token',
            'client_id'     => 'client_test2',
            'redirect_uri'  => '/redirect',
            'scope'         => 'test',
            'state'         => $state,
        ];
        $request = $this->buildServerRequest(
            'GET',
            '/authorize?' . http_build_query($params),
            '',
            [],
            [],
            $params
        );

        $authMiddleware  = new AuthorizationMiddleware($this->authServer, $this->responseFactory);
        $authHandler     = new AuthorizationHandler($this->authServer, $this->responseFactory);
        $consumerHandler = $this->buildConsumerAuthMiddleware($authHandler);

        $response = $authMiddleware->process($request, $consumerHandler);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertTrue($response->hasHeader('Location'));
        [$url, $fragment] = explode('#', $response->getHeader('Location')[0]);
        $this->assertEquals($params['redirect_uri'], $url);
        parse_str($fragment, $data);
        $this->assertTrue(isset($data['access_token']));
        $this->assertTrue(isset($data['expires_in']));
        $this->assertTrue(isset($data['token_type']));
        $this->assertEquals('bearer', strtolower($data['token_type']));
        $this->assertFalse(isset($data['refresh_token']));
        $this->assertTrue(isset($data['state']));
        $this->assertEquals($state, $data['state']);
    }

    /**
     * Test the Refresh Token Grant
     *
     * @see https://oauth2.thephpleague.com/authorization-server/refresh-token-grant/
     *
     * @depends testProcessFromAuthorizationCode
     */
    public function testProcessRefreshTokenGrant(string $refreshToken)
    {
        $grant = new RefreshTokenGrant($this->refreshTokenRepository);
        $grant->setRefreshTokenTTL(new DateInterval('P1M')); // new refresh tokens will expire after 1 month

        // Enable the refresh token grant on the server
        $this->authServer->enableGrantType(
            $grant,
            new DateInterval('PT1H') // new access tokens will expire after an hour
        );
        // Server request
        $params  = [
            'grant_type'    => 'refresh_token',
            'client_id'     => 'client_test2',
            'client_secret' => 'test',
            'refresh_token' => $refreshToken,
            'scope'         => 'test',
        ];
        $request = $this->buildServerRequest(
            'POST',
            '/access_token',
            http_build_query($params),
            $params,
            ['Content-Type' => 'application/x-www-form-urlencoded']
        );

        $handler = new TokenEndpointHandler(
            $this->authServer,
            $this->responseFactory
        );

        $response = $handler->handle($request);

        $this->assertEquals(200, $response->getStatusCode());
        $content = json_decode((string) $response->getBody());
        $this->assertEquals('Bearer', $content->token_type);
        $this->assertIsInt($content->expires_in);
        $this->assertNotEmpty($content->access_token);
        $this->assertNotEmpty($content->refresh_token);
    }

    private function buildConsumerAuthMiddleware(AuthorizationHandler $authHandler): object
    {
        return new class ($authHandler) implements RequestHandlerInterface
        {
            /** @var AuthorizationHandler */
            private $handler;

            public function __construct(AuthorizationHandler $handler)
            {
                $this->handler = $handler;
            }

            public function handle(
                ServerRequestInterface $request
            ): ResponseInterface {
                $authRequest = $request->getAttribute(AuthorizationRequest::class);
                assert($authRequest instanceof AuthorizationRequest);
                $authRequest->setUser(new UserEntity('test'));
                $authRequest->setAuthorizationApproved(true);

                return $this->handler->handle(
                    $request->withAttribute(AuthorizationRequest::class, $authRequest)
                );
            }
        };
    }

    /**
     * Build a ServerRequest object
     */
    protected function buildServerRequest(
        string $method,
        string $url,
        string $body,
        array $params,
        array $headers = [],
        array $queryParams = []
    ): ServerRequest {
        $stream = new Stream('php://temp', 'w');
        $stream->write($body);

        return new ServerRequest(
            [],
            [],
            $url,
            $method,
            $stream,
            $headers,
            [],
            $queryParams,
            $params
        );
    }
}
