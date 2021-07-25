<?php

declare(strict_types=1);

namespace MezzioTest\Authentication\OAuth2;

use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\RequestTypes\AuthorizationRequest;
use Mezzio\Authentication\OAuth2\AuthorizationMiddleware;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;

class AuthorizationMiddlewareTest extends TestCase
{
    use ProphecyTrait;

    /** @var AuthorizationRequest|ObjectProphecy */
    private $authRequest;

    /** @var AuthorizationServer|ObjectProphecy */
    private $authServer;

    /** @var RequestHandlerInterface|ObjectProphecy */
    private $handler;

    /** @var ResponseInterface&MockObject */
    private $response;

    /** @var callable */
    private $responseFactory;

    /** @var ServerRequestInterface|ObjectProphecy */
    private $serverRequest;

    protected function setUp(): void
    {
        $this->authServer      = $this->prophesize(AuthorizationServer::class);
        $this->response        = $this->createMock(ResponseInterface::class);
        $this->serverRequest   = $this->prophesize(ServerRequestInterface::class);
        $this->authRequest     = $this->prophesize(AuthorizationRequest::class);
        $this->handler         = $this->prophesize(RequestHandlerInterface::class);
        $this->responseFactory = function () {
            return $this->response;
        };
    }

    public function testConstructor()
    {
        $middleware = new AuthorizationMiddleware(
            $this->authServer->reveal(),
            $this->responseFactory
        );

        $this->assertInstanceOf(AuthorizationMiddleware::class, $middleware);
        $this->assertInstanceOf(MiddlewareInterface::class, $middleware);
    }

    public function testProcess()
    {
        $this->authRequest
            ->setUser(Argument::any())
            ->shouldNotBeCalled(); // Ths middleware must not provide a user entity
        $this->authRequest
            ->setAuthorizationApproved(false) // Expect approval to be set to false only
            ->willReturn(null);

        // Mock a valid authorization request
        $this->authServer
            ->validateAuthorizationRequest($this->serverRequest->reveal())
            ->willReturn($this->authRequest->reveal());

        // Mock a instance immutability when the authorization request
        // is populated
        $newRequest = $this->prophesize(ServerRequestInterface::class);
        $this->serverRequest
             ->withAttribute(AuthorizationRequest::class, $this->authRequest->reveal())
             ->willReturn($newRequest->reveal());

        // Expect the handler to be called with the new modified request,
        // that contains the auth request attribute
        $handlerResponse = $this->createMock(ResponseInterface::class);
        $this->handler
            ->handle($newRequest->reveal())
            ->willReturn($handlerResponse);

        $middleware = new AuthorizationMiddleware(
            $this->authServer->reveal(),
            $this->responseFactory
        );
        $response   = $middleware->process(
            $this->serverRequest->reveal(),
            $this->handler->reveal()
        );

        $this->assertSame($handlerResponse, $response);
    }

    public function testAuthorizationRequestRaisingOAuthServerExceptionGeneratesResponseFromException()
    {
        $oauthServerException = $this->createMock(OAuthServerException::class);
        $oauthServerException
            ->method('generateHttpResponse')
            ->with($this->response)
            ->willReturnArgument(0);

        $this->authServer
            ->validateAuthorizationRequest($this->serverRequest->reveal())
            ->willThrow($oauthServerException);

        $middleware = new AuthorizationMiddleware(
            $this->authServer->reveal(),
            $this->responseFactory
        );

        $this->response
            ->method('withStatus')
            ->willReturnSelf();

        $result = $middleware->process(
            $this->serverRequest->reveal(),
            $this->handler->reveal()
        );

        $this->assertSame($this->response, $result);
    }

    public function testAuthorizationRequestRaisingUnknownExceptionGeneratesResponseFromException()
    {
        $body = $this->prophesize(StreamInterface::class);
        $body
            ->write(Argument::containingString('oauth2 server error'))
            ->shouldBeCalled();

        $this->response
            ->expects(self::once())
            ->method('getBody')
            ->willReturn($body->reveal());
        $this->response
            ->expects(self::once())
            ->method('withHeader')
            ->willReturnSelf();

        $this->response
            ->expects(self::exactly(2))
            ->method('withStatus')
            ->withConsecutive([200], [500])
            ->willReturnSelf();

        $exception = new RuntimeException('oauth2 server error');

        $this->authServer
            ->validateAuthorizationRequest($this->serverRequest->reveal())
            ->willThrow($exception);

        $middleware = new AuthorizationMiddleware(
            $this->authServer->reveal(),
            $this->responseFactory
        );

        $response = $middleware->process(
            $this->serverRequest->reveal(),
            $this->handler->reveal()
        );

        $this->assertSame($this->response, $response);
    }
}
