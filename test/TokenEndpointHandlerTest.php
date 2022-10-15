<?php

declare(strict_types=1);

namespace MezzioTest\Authentication\OAuth2;

use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use Mezzio\Authentication\OAuth2\Exception\RuntimeException;
use Mezzio\Authentication\OAuth2\TokenEndpointHandler;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @covers \Mezzio\Authentication\OAuth2\TokenEndpointHandler
 */
class TokenEndpointHandlerTest extends TestCase
{
    /** @return callable(): ResponseInterface */
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

    public function testHandleUsesAuthorizationServer(): void
    {
        $server   = $this->createMock(AuthorizationServer::class);
        $request  = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $response
            ->method('withStatus')
            ->willReturnSelf();
        $expectedResponse = $response;

        $server->expects(self::once())
            ->method('respondToAccessTokenRequest')
            ->with($request, $expectedResponse)
            ->willReturn($expectedResponse);

        $subject = new TokenEndpointHandler($server, $this->createResponseFactory($expectedResponse));
        self::assertSame($expectedResponse, $subject->handle($request));
    }

    public function testOAuthExceptionProducesResult(): void
    {
        $server   = $this->createMock(AuthorizationServer::class);
        $request  = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $response
            ->method('withStatus')
            ->willReturnSelf();
        $exception        = $this->createMock(OAuthServerException::class);
        $expectedResponse = $response;

        $server->expects(self::once())
            ->method('respondToAccessTokenRequest')
            ->willThrowException($exception);

        $exception->expects(self::once())
            ->method('generateHttpResponse')
            ->with($expectedResponse)
            ->willReturn($expectedResponse);

        $subject = new TokenEndpointHandler($server, $this->createResponseFactory($expectedResponse));
        self::assertSame($expectedResponse, $subject->handle($request));
    }

    public function testGenericExceptionsFallsThrough(): void
    {
        $server    = $this->createMock(AuthorizationServer::class);
        $request   = $this->createMock(ServerRequestInterface::class);
        $exception = new RuntimeException();

        $server->expects(self::once())
            ->method('respondToAccessTokenRequest')
            ->willThrowException($exception);

        $subject = new TokenEndpointHandler($server, $this->createResponseFactory());

        $this->expectException(RuntimeException::class);
        $subject->handle($request);
    }
}
