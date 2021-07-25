<?php

declare(strict_types=1);

namespace MezzioTest\Authentication\OAuth2;

use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\ResourceServer;
use Mezzio\Authentication\AuthenticationInterface;
use Mezzio\Authentication\DefaultUser;
use Mezzio\Authentication\OAuth2\OAuth2Adapter;
use Mezzio\Authentication\UserInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class OAuth2AdapterTest extends TestCase
{
    use ProphecyTrait;

    /** @var ResourceServer|ObjectProphecy */
    private $resourceServer;

    /** @var ResponseInterface&MockObject */
    private $response;

    /** @var callable */
    private $responseFactory;

    /** @var callable */
    private $userFactory;

    protected function setUp(): void
    {
        $this->resourceServer  = $this->prophesize(ResourceServer::class);
        $this->response        = $this->createMock(ResponseInterface::class);
        $this->responseFactory = function (): ResponseInterface {
            return $this->response;
        };
        $this->userFactory     = function (
            string $identity,
            array $roles = [],
            array $details = []
        ) {
            return new DefaultUser($identity, $roles, $details);
        };
    }

    public function testConstructor()
    {
        $adapter = new OAuth2Adapter(
            $this->resourceServer->reveal(),
            $this->responseFactory,
            $this->userFactory
        );
        $this->assertInstanceOf(OAuth2Adapter::class, $adapter);
        $this->assertInstanceOf(AuthenticationInterface::class, $adapter);
    }

    public function testOAuthServerExceptionRaisedDuringAuthenticateResultsInInvalidAuthentication()
    {
        $request = $this->prophesize(ServerRequestInterface::class);

        $exception = $this->prophesize(OAuthServerException::class);

        $this->resourceServer
            ->validateAuthenticatedRequest(Argument::that([$request, 'reveal']))
            ->willThrow($exception->reveal());

        $adapter = new OAuth2Adapter(
            $this->resourceServer->reveal(),
            $this->responseFactory,
            $this->userFactory
        );

        $this->assertNull($adapter->authenticate($request->reveal()));
    }

    public function testAuthenticateReturnsNullIfResourceServerDoesNotProduceAUserIdOrClientId()
    {
        $request = $this->prophesize(ServerRequestInterface::class);
        $request->getAttribute('oauth_user_id', null)->willReturn(null);
        $request->getAttribute('oauth_client_id', null)->willReturn(null);

        $this->resourceServer
            ->validateAuthenticatedRequest(Argument::that([$request, 'reveal']))
            ->will([$request, 'reveal']);

        $adapter = new OAuth2Adapter(
            $this->resourceServer->reveal(),
            $this->responseFactory,
            $this->userFactory
        );

        $this->assertNull($adapter->authenticate($request->reveal()));
    }

    public function testAuthenticateReturnsAUserIfTheResourceServerProducesAUserId()
    {
        $request = $this->prophesize(ServerRequestInterface::class);
        $request->getAttribute('oauth_user_id', null)->willReturn('some-identifier');
        $request->getAttribute('oauth_client_id', null)->willReturn(null);
        $request->getAttribute('oauth_access_token_id', null)->willReturn(null);
        $request->getAttribute('oauth_scopes', null)->willReturn(null);

        $this->resourceServer
            ->validateAuthenticatedRequest(Argument::that([$request, 'reveal']))
            ->will([$request, 'reveal']);

        $adapter = new OAuth2Adapter(
            $this->resourceServer->reveal(),
            $this->responseFactory,
            $this->userFactory
        );

        $user = $adapter->authenticate($request->reveal());

        $this->assertInstanceOf(UserInterface::class, $user);
        $this->assertSame('some-identifier', $user->getIdentity());
        $this->assertSame([], $user->getRoles());
    }

    public function testAuthenticateReturnsNullIfTheResourceServerProducesAClientIdOnly()
    {
        $request = $this->prophesize(ServerRequestInterface::class);
        $request->getAttribute('oauth_user_id', null)->willReturn(null);
        $request->getAttribute('oauth_client_id', null)->willReturn('some-identifier');
        $request->getAttribute('oauth_access_token_id', null)->willReturn(null);
        $request->getAttribute('oauth_scopes', null)->willReturn(null);

        $this->resourceServer
            ->validateAuthenticatedRequest(Argument::that([$request, 'reveal']))
            ->will([$request, 'reveal']);

        $adapter = new OAuth2Adapter(
            $this->resourceServer->reveal(),
            $this->responseFactory,
            $this->userFactory
        );

        $user = $adapter->authenticate($request->reveal());
        $this->assertNull($user);
    }

    public function testUnauthorizedResponseProducesAResponseWithAWwwAuthenticateHeader()
    {
        $request = $this->prophesize(ServerRequestInterface::class)->reveal();

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
            $this->resourceServer->reveal(),
            $this->responseFactory,
            $this->userFactory
        );

        $this->assertSame(
            $this->response,
            $adapter->unauthorizedResponse($request)
        );
    }
}
