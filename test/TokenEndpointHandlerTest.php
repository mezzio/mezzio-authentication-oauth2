<?php

declare(strict_types=1);

namespace MezzioTest\Authentication\OAuth2;

use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use Mezzio\Authentication\OAuth2\Exception\RuntimeException;
use Mezzio\Authentication\OAuth2\TokenEndpointHandler;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @covers \Mezzio\Authentication\OAuth2\TokenEndpointHandler
 */
class TokenEndpointHandlerTest extends TestCase
{
    use ProphecyTrait;

    private function createResponseFactory(?ResponseInterface $response = null): callable
    {
        return function () use ($response): ResponseInterface {
            if ($response !== null) {
                return $response;
            }
            $response = $this->createMock(ResponseInterface::class);
            $response->method('withStatus')->willReturnSelf();
            return $response;
        };
    }

    public function testHandleUsesAuthorizationServer()
    {
        $server   = $this->prophesize(AuthorizationServer::class);
        $request  = $this->prophesize(ServerRequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $response
            ->method('withStatus')
            ->willReturnSelf();
        $expectedResponse = $response;

        $server->respondToAccessTokenRequest($request->reveal(), $expectedResponse)
            ->shouldBeCalled()
            ->willReturn($expectedResponse);

        $subject = new TokenEndpointHandler($server->reveal(), $this->createResponseFactory($expectedResponse));
        self::assertSame($expectedResponse, $subject->handle($request->reveal()));
    }

    public function testOAuthExceptionProducesResult()
    {
        $server   = $this->prophesize(AuthorizationServer::class);
        $request  = $this->prophesize(ServerRequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $response
            ->method('withStatus')
            ->willReturnSelf();
        $exception        = $this->prophesize(OAuthServerException::class);
        $expectedResponse = $response;

        $server->respondToAccessTokenRequest(Argument::cetera())
            ->willThrow($exception->reveal());

        $exception->generateHttpResponse($expectedResponse, Argument::cetera())
            ->shouldBeCalled()
            ->willReturn($expectedResponse);

        $subject = new TokenEndpointHandler($server->reveal(), $this->createResponseFactory($expectedResponse));
        self::assertSame($expectedResponse, $subject->handle($request->reveal()));
    }

    public function testGenericExceptionsFallsThrough()
    {
        $server    = $this->prophesize(AuthorizationServer::class);
        $request   = $this->prophesize(ServerRequestInterface::class);
        $exception = new RuntimeException();

        $server->respondToAccessTokenRequest(Argument::cetera())
            ->willThrow($exception);

        $subject = new TokenEndpointHandler($server->reveal(), $this->createResponseFactory());

        $this->expectException(RuntimeException::class);
        $subject->handle($request->reveal());
    }
}
