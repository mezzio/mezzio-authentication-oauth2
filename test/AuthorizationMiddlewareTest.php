<?php

declare(strict_types=1);

namespace MezzioTest\Authentication\OAuth2;

use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\RequestTypes\AuthorizationRequest;
use Mezzio\Authentication\OAuth2\AuthorizationMiddleware;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;

class AuthorizationMiddlewareTest extends TestCase
{
    /** @var AuthorizationRequest&MockObject */
    private AuthorizationRequest $authRequest;

    /** @var AuthorizationServer&MockObject */
    private AuthorizationServer $authServer;

    /** @var RequestHandlerInterface&MockObject */
    private RequestHandlerInterface $handler;

    /** @var ResponseInterface&MockObject */
    private ResponseInterface $response;

    /** @var callable(): ResponseInterface */
    private $responseFactory;

    /** @var ServerRequestInterface&MockObject */
    private ServerRequestInterface $serverRequest;

    protected function setUp(): void
    {
        $this->authServer      = $this->createMock(AuthorizationServer::class);
        $this->response        = $this->createMock(ResponseInterface::class);
        $this->serverRequest   = $this->createMock(ServerRequestInterface::class);
        $this->authRequest     = $this->createMock(AuthorizationRequest::class);
        $this->handler         = $this->createMock(RequestHandlerInterface::class);
        $this->responseFactory = fn(): ResponseInterface => $this->response;
    }

    public function testConstructor(): void
    {
        $middleware = new AuthorizationMiddleware(
            $this->authServer,
            $this->responseFactory
        );

        self::assertInstanceOf(AuthorizationMiddleware::class, $middleware);
        self::assertInstanceOf(MiddlewareInterface::class, $middleware);
    }

    public function testProcess(): void
    {
        $this->authRequest->expects(self::never())
            ->method('setUser'); // Ths middleware must not provide a user entity

        $this->authRequest->expects(self::once())
            ->method('setAuthorizationApproved')
            ->with(false); // Expect approval to be set to false only

        // Mock a valid authorization request
        $this->authServer->expects(self::once())
            ->method('validateAuthorizationRequest')
            ->with($this->serverRequest)
            ->willReturn($this->authRequest);

        // Mock a instance immutability when the authorization request
        // is populated
        $newRequest = $this->createMock(ServerRequestInterface::class);
        $this->serverRequest->expects(self::once())
            ->method('withAttribute')
            ->with(AuthorizationRequest::class, $this->authRequest)
            ->willReturn($newRequest);

        // Expect the handler to be called with the new modified request,
        // that contains the auth request attribute
        $handlerResponse = $this->createMock(ResponseInterface::class);
        $this->handler->expects(self::once())
            ->method('handle')
            ->with($newRequest)
            ->willReturn($handlerResponse);

        $middleware = new AuthorizationMiddleware(
            $this->authServer,
            $this->responseFactory
        );
        $response   = $middleware->process(
            $this->serverRequest,
            $this->handler
        );

        self::assertSame($handlerResponse, $response);
    }

    public function testAuthorizationRequestRaisingOAuthServerExceptionGeneratesResponseFromException(): void
    {
        $oauthServerException = $this->createMock(OAuthServerException::class);
        $oauthServerException->expects(self::once())
            ->method('generateHttpResponse')
            ->with($this->response)
            ->willReturnArgument(0);

        $this->authServer->expects(self::once())
            ->method('validateAuthorizationRequest')
            ->with($this->serverRequest)
            ->willThrowException($oauthServerException);

        $middleware = new AuthorizationMiddleware(
            $this->authServer,
            $this->responseFactory
        );

        $this->response
            ->method('withStatus')
            ->willReturnSelf();

        $result = $middleware->process(
            $this->serverRequest,
            $this->handler
        );

        self::assertSame($this->response, $result);
    }

    public function testAuthorizationRequestRaisingUnknownExceptionGeneratesResponseFromException(): void
    {
        $body = $this->createMock(StreamInterface::class);
        $body->expects(self::once())
            ->method('write')
            ->with(self::stringContains('oauth2 server error'));

        $this->response
            ->expects(self::once())
            ->method('getBody')
            ->willReturn($body);
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

        $this->authServer->expects(self::once())
            ->method('validateAuthorizationRequest')
            ->with($this->serverRequest)
            ->willThrowException($exception);

        $middleware = new AuthorizationMiddleware(
            $this->authServer,
            $this->responseFactory
        );

        $response = $middleware->process(
            $this->serverRequest,
            $this->handler
        );

        self::assertSame($this->response, $response);
    }
}
