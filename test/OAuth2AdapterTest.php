<?php

declare(strict_types=1);

namespace MezzioTest\Authentication\OAuth2;

use Laminas\Diactoros\ServerRequest;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\ResourceServer;
use Mezzio\Authentication\AuthenticationInterface;
use Mezzio\Authentication\DefaultUser;
use Mezzio\Authentication\OAuth2\OAuth2Adapter;
use Mezzio\Authentication\UserInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class OAuth2AdapterTest extends TestCase
{
    /** @var ResourceServer&MockObject */
    private ResourceServer $resourceServer;

    /** @var ResponseInterface&MockObject */
    private ResponseInterface $response;

    /** @var callable(): ResponseInterface */
    private $responseFactory;

    /** @var callable(string, array, array): DefaultUser */
    private $userFactory;

    protected function setUp(): void
    {
        $this->resourceServer  = $this->createMock(ResourceServer::class);
        $this->response        = $this->createMock(ResponseInterface::class);
        $this->responseFactory = fn(): ResponseInterface => $this->response;
        $this->userFactory     = static fn(string $identity, array $roles = [], array $details = []): DefaultUser
                => new DefaultUser($identity, $roles, $details);
    }

    public function testConstructor(): void
    {
        $adapter = new OAuth2Adapter(
            $this->resourceServer,
            $this->responseFactory,
            $this->userFactory
        );
        self::assertInstanceOf(OAuth2Adapter::class, $adapter);
        self::assertInstanceOf(AuthenticationInterface::class, $adapter);
    }

    public function testOAuthServerExceptionRaisedDuringAuthenticateResultsInInvalidAuthentication(): void
    {
        $request   = $this->createMock(ServerRequestInterface::class);
        $exception = $this->createMock(OAuthServerException::class);

        $this->resourceServer->expects(self::once())
            ->method('validateAuthenticatedRequest')
            ->with($request)
            ->willThrowException($exception);

        $adapter = new OAuth2Adapter(
            $this->resourceServer,
            $this->responseFactory,
            $this->userFactory
        );

        self::assertNull($adapter->authenticate($request));
    }

    public function testAuthenticateReturnsNullIfResourceServerDoesNotProduceAUserIdOrClientId(): void
    {
        $request = new ServerRequest();
        $this->resourceServer->expects(self::once())
            ->method('validateAuthenticatedRequest')
            ->with($request)
            ->willReturn($request);

        $adapter = new OAuth2Adapter(
            $this->resourceServer,
            $this->responseFactory,
            $this->userFactory
        );

        self::assertNull($adapter->authenticate($request));
    }

    public function testAuthenticateReturnsAUserIfTheResourceServerProducesAUserId(): void
    {
        $request = (new ServerRequest())->withAttribute('oauth_user_id', 'some-identifier');

        $this->resourceServer->expects(self::once())
            ->method('validateAuthenticatedRequest')
            ->with($request)
            ->willReturn($request);

        $adapter = new OAuth2Adapter(
            $this->resourceServer,
            $this->responseFactory,
            $this->userFactory
        );

        $user = $adapter->authenticate($request);

        self::assertInstanceOf(UserInterface::class, $user);
        self::assertSame('some-identifier', $user->getIdentity());
        self::assertSame([], $user->getRoles());
    }

    public function testAuthenticateReturnsNullIfTheResourceServerProducesAClientIdOnly(): void
    {
        $request = (new ServerRequest())->withAttribute('oauth_client_id', 'some-identifier');

        $this->resourceServer->expects(self::once())
            ->method('validateAuthenticatedRequest')
            ->with($request)
            ->willReturn($request);

        $adapter = new OAuth2Adapter(
            $this->resourceServer,
            $this->responseFactory,
            $this->userFactory
        );

        $user = $adapter->authenticate($request);
        self::assertNull($user);
    }

    public function testUnauthorizedResponseProducesAResponseWithAWwwAuthenticateHeader(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);

        $this->response
            ->expects(self::once())
            ->method('withHeader')
            ->with('WWW-Authenticate', 'Bearer realm="OAuth2 token"')
            ->willReturnSelf();
        $this->response
            ->expects(self::once())
            ->method('withStatus')
            ->with(401)
            ->willReturnSelf();

        $adapter = new OAuth2Adapter(
            $this->resourceServer,
            $this->responseFactory,
            $this->userFactory
        );

        self::assertSame(
            $this->response,
            $adapter->unauthorizedResponse($request)
        );
    }
}
