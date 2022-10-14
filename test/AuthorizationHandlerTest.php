<?php

declare(strict_types=1);

namespace Mezzio\Authentication\OAuth2;

use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\RequestTypes\AuthorizationRequest;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
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
        $server           = $this->prophesize(AuthorizationServer::class);
        $response         = $this->createMock(ResponseInterface::class);
        $authRequest      = $this->prophesize(AuthorizationRequest::class);
        $request          = $this->prophesize(ServerRequestInterface::class);
        $expectedResponse = $response;
        $response
            ->method('withStatus')
            ->willReturnSelf();

        $request->getAttribute(AuthorizationRequest::class)
            ->willReturn($authRequest->reveal());

        $server->completeAuthorizationRequest($authRequest->reveal(), $expectedResponse)
            ->shouldBeCalled()
            ->willReturn($expectedResponse);

        $subject = new AuthorizationHandler($server->reveal(), static fn(): ResponseInterface => $expectedResponse);

        self::assertSame($expectedResponse, $subject->handle($request->reveal()));
    }

    public function testInvalidResponseFactoryThrowsTypeError(): void
    {
        $server      = $this->prophesize(AuthorizationServer::class);
        $authRequest = $this->prophesize(AuthorizationRequest::class);
        $request     = $this->prophesize(ServerRequestInterface::class);

        $request->getAttribute(AuthorizationRequest::class)
            ->willReturn($authRequest->reveal());

        $server->completeAuthorizationRequest(Argument::any())
            ->shouldNotBeCalled();

        $subject = new AuthorizationHandler($server->reveal(), static fn(): stdClass => new stdClass());

        $this->expectException(TypeError::class);
        $subject->handle($request->reveal());
    }
}
