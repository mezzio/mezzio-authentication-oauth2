<?php

declare(strict_types=1);

namespace Mezzio\Authentication\OAuth2;

use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\RequestTypes\AuthorizationRequest;
use Mezzio\Authentication\OAuth2\Response\CallableResponseFactoryDecorator;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function is_callable;

/**
 * Handles the already validated and competed authorization request
 *
 * This will perform the required redirect to the requesting party.
 * The request must provide an attribute `League\OAuth2\Server\AuthorizationServer`
 * that contains the validated OAuth2 request
 *
 * @see https://tools.ietf.org/html/rfc6749#section-3.1.1
 */
class AuthorizationHandler implements RequestHandlerInterface
{
    private AuthorizationServer $server;

    private ResponseFactoryInterface $responseFactory;

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

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $authRequest = $request->getAttribute(AuthorizationRequest::class);
        return $this->server->completeAuthorizationRequest(
            $authRequest,
            $this->responseFactory->createResponse()
        );
    }
}
