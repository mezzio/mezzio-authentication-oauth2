<?php

/**
 * @see       https://github.com/mezzio/mezzio-authentication-oauth2 for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-authentication-oauth2/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-authentication-oauth2/blob/master/LICENSE.md New BSD License
 */

namespace MezzioTest\Authentication\OAuth2;

use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\ResourceServer;
use Mezzio\Authentication\AuthenticationInterface;
use Mezzio\Authentication\OAuth2\OAuth2Adapter;
use Mezzio\Authentication\UserInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class OAuth2AdapterTest extends TestCase
{
    public function setUp()
    {
        $this->resourceServer = $this->prophesize(ResourceServer::class);
        $this->response       = $this->prophesize(ResponseInterface::class);
    }

    public function testConstructor()
    {
        $adapter = new OAuth2Adapter(
            $this->resourceServer->reveal(),
            $this->response->reveal()
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
            $this->response->reveal()
        );

        $this->assertNull($adapter->authenticate($request->reveal()));
    }

    public function testAuthenticateReturnsNullIfResourceServerDoesNotProduceAUserId()
    {
        $request = $this->prophesize(ServerRequestInterface::class);
        $request->getAttribute('oauth_user_id', false)->willReturn(false);

        $this->resourceServer
            ->validateAuthenticatedRequest(Argument::that([$request, 'reveal']))
            ->will([$request, 'reveal']);

        $adapter = new OAuth2Adapter(
            $this->resourceServer->reveal(),
            $this->response->reveal()
        );

        $this->assertNull($adapter->authenticate($request->reveal()));
    }

    public function testAuthenticateReturnsAUserIfTheResourceServerProducesAUserId()
    {
        $request = $this->prophesize(ServerRequestInterface::class);
        $request->getAttribute('oauth_user_id', false)->willReturn('some-identifier');

        $this->resourceServer
            ->validateAuthenticatedRequest(Argument::that([$request, 'reveal']))
            ->will([$request, 'reveal']);

        $adapter = new OAuth2Adapter(
            $this->resourceServer->reveal(),
            $this->response->reveal()
        );

        $user = $adapter->authenticate($request->reveal());

        $this->assertInstanceOf(UserInterface::class, $user);
        $this->assertSame('some-identifier', $user->getIdentity());
        $this->assertSame([], $user->getUserRoles());
    }

    public function testUnauthorizedResponseProducesAResponseWithAWwwAuthenticateHeader()
    {
        $request = $this->prophesize(ServerRequestInterface::class)->reveal();

        $this->response
            ->withHeader('WWW-Authenticate', 'Bearer token-example')
            ->will([$this->response, 'reveal']);
        $this->response
            ->withStatus(401)
            ->will([$this->response, 'reveal']);

        $adapter = new OAuth2Adapter(
            $this->resourceServer->reveal(),
            $this->response->reveal()
        );

        $this->assertSame(
            $this->response->reveal(),
            $adapter->unauthorizedResponse($request)
        );
    }
}
