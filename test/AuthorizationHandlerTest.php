<?php

declare(strict_types=1);

namespace MezzioTest\Authentication\OAuth2;

use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\RequestTypes\AuthorizationRequestInterface;
use Mezzio\Authentication\OAuth2\AuthorizationHandler;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use stdClass;
use TypeError;

#[CoversClass(AuthorizationHandler::class)]
final class AuthorizationHandlerTest extends TestCase
{
    public function testHandleUsesAuthorizationServerService(): void
    {
        $server           = $this->createMock(AuthorizationServer::class);
        $response         = $this->createMock(ResponseInterface::class);
        $authRequest      = $this->createMock(AuthorizationRequestInterface::class);
        $request          = $this->createMock(ServerRequestInterface::class);
        $expectedResponse = $response;
        $response
            ->method('withStatus')
            ->willReturnSelf();

        $request->method('getAttribute')
            ->with(AuthorizationRequestInterface::class)
            ->willReturn($authRequest);

        $server->expects(self::once())
            ->method('completeAuthorizationRequest')
            ->with($authRequest, $expectedResponse)
            ->willReturn($expectedResponse);

        $subject = new AuthorizationHandler($server, static fn(): ResponseInterface => $expectedResponse);

        self::assertSame($expectedResponse, $subject->handle($request));
    }

    public function testInvalidResponseFactoryThrowsTypeError(): void
    {
        $server      = $this->createMock(AuthorizationServer::class);
        $authRequest = $this->createMock(AuthorizationRequestInterface::class);
        $request     = $this->createMock(ServerRequestInterface::class);

        $request->method('getAttribute')
            ->with(AuthorizationRequestInterface::class)
            ->willReturn($authRequest);

        $server->expects(self::never())
            ->method('completeAuthorizationRequest');

        /** @psalm-suppress InvalidArgument */
        $subject = new AuthorizationHandler($server, static fn(): stdClass => new stdClass());

        $this->expectException(TypeError::class);
        $subject->handle($request);
    }
}
