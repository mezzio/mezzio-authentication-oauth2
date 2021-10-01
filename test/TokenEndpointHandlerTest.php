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

    private function createResponseFactory(ResponseInterface $response = null): callable
    {
        return function () use ($response): ResponseInterface {
            return $response ?? $this->prophesize(ResponseInterface::class)->reveal();
        };
    }

    public function testHandleUsesAuthorizationServer()
    {
        $server = $this->prophesize(AuthorizationServer::class);
        $request = $this->prophesize(ServerRequestInterface::class);
        $response = $this->prophesize(ResponseInterface::class);
        $expectedResponse = $response->reveal();

        $server->respondToAccessTokenRequest($request->reveal(), $expectedResponse)
            ->shouldBeCalled()
            ->willReturn($expectedResponse);

        $subject = new TokenEndpointHandler($server->reveal(), $this->createResponseFactory($expectedResponse));
        self::assertSame($expectedResponse, $subject->handle($request->reveal()));
    }

    public function testOAuthExceptionProducesResult()
    {
        $server = $this->prophesize(AuthorizationServer::class);
        $request = $this->prophesize(ServerRequestInterface::class);
        $response = $this->prophesize(ResponseInterface::class);
        $exception = $this->prophesize(OAuthServerException::class);
        $expectedResponse = $response->reveal();

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
        $server = $this->prophesize(AuthorizationServer::class);
        $request = $this->prophesize(ServerRequestInterface::class);
        $exception = new RuntimeException();

        $server->respondToAccessTokenRequest(Argument::cetera())
            ->willThrow($exception);

        $subject = new TokenEndpointHandler($server->reveal(), $this->createResponseFactory());

        $this->expectException(RuntimeException::class);
        $subject->handle($request->reveal());
    }
}
