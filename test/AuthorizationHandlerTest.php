<?php

declare(strict_types=1);

namespace MezzioTest\Authentication\OAuth2;

use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\RequestTypes\AuthorizationRequest;
use Mezzio\Authentication\OAuth2\AuthorizationHandler;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use stdClass;
use TypeError;

/**
 * @covers \Mezzio\Authentication\OAuth2\AuthorizationHandler
 */
class AuthorizationHandlerTest extends TestCase
{
    use ProphecyTrait;

    public function testHandleUsesAuthorizationServerService(): void
    {
        $server           = $this->createMock(AuthorizationServer::class);
        $response         = $this->createMock(ResponseInterface::class);
        $authRequest      = $this->createMock(AuthorizationRequest::class);
        $request          = $this->createMock(ServerRequestInterface::class);
        $expectedResponse = $response;
        $response
            ->method('withStatus')
            ->willReturnSelf();

        $request->method('getAttribute')
            ->with(AuthorizationRequest::class)
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
        $authRequest = $this->createMock(AuthorizationRequest::class);
        $request     = $this->createMock(ServerRequestInterface::class);

        $request->method('getAttribute')
            ->with(AuthorizationRequest::class)
            ->willReturn($authRequest);

        $server->expects(self::never())
            ->method('completeAuthorizationRequest');

        /** @psalm-suppress InvalidArgument */
        $subject = new AuthorizationHandler($server, static fn(): stdClass => new stdClass());

        $this->expectException(TypeError::class);
        $subject->handle($request);
    }
}
