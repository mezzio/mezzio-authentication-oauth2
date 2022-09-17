<?php

declare(strict_types=1);

namespace Mezzio\Authentication\OAuth2;

use Exception as BaseException;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\RequestTypes\AuthorizationRequest;
use Mezzio\Authentication\OAuth2\Response\CallableResponseFactoryDecorator;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function is_callable;

/**
 * Implements OAuth2 authorization request validation
 *
 * Performs checks if the OAuth authorization request is valid and populates it
 * to the next handler via the request object as attribute with the key
 * `League\OAuth2\Server\AuthorizationServer`
 *
 * The next handler should take care of checking the resource owner's authentication and
 * consent. It may intercept to ensure authentication and consent before populating it to
 * the authorization request object
 *
 * @see https://oauth2.thephpleague.com/authorization-server/auth-code-grant/
 * @see https://oauth2.thephpleague.com/authorization-server/implicit-grant/
 */
class AuthorizationMiddleware implements MiddlewareInterface
{
    /** @var AuthorizationServer */
    protected $server;

    /** @var ResponseFactoryInterface */
    protected $responseFactory;

    /**
     * @param (callable():ResponseInterface)|ResponseFactoryInterface $responseFactory
     */
    public function __construct(AuthorizationServer $server, $responseFactory)
    {
        $this->server = $server;
        if (is_callable($responseFactory)) {
            $responseFactory = new CallableResponseFactoryDecorator(
                static fn(): ResponseInterface => $responseFactory()
            );
        }

        $this->responseFactory = $responseFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            $authRequest = $this->server->validateAuthorizationRequest($request);

            // The next handler must take care of providing the
            // authenticated user and the approval
            $authRequest->setAuthorizationApproved(false);

            return $handler->handle($request->withAttribute(AuthorizationRequest::class, $authRequest));
        } catch (OAuthServerException $exception) {
            $response = $this->responseFactory->createResponse();
            // The validation throws this exception if the request is not valid
            // for example when the client id is invalid
            return $exception->generateHttpResponse($response);
        } catch (BaseException $exception) {
            $response = $this->responseFactory->createResponse();
            return (new OAuthServerException($exception->getMessage(), 0, 'unknown_error', 500))
                ->generateHttpResponse($response);
        }
    }
}
