<?php

declare(strict_types=1);

namespace Mezzio\Authentication\OAuth2;

use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use Mezzio\Authentication\OAuth2\Response\CallableResponseFactoryDecorator;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function is_callable;

/**
 * Provides an OAuth2 token endpoint implementation
 *
 * The token endpoint is required to obtain the access token and optionally a refresh token.
 *
 * @see https://tools.ietf.org/html/rfc6749#section-3.2
 * @see https://oauth2.thephpleague.com/authorization-server/client-credentials-grant/
 * @see https://oauth2.thephpleague.com/authorization-server/resource-owner-password-credentials-grant/
 * @see https://oauth2.thephpleague.com/authorization-server/refresh-token-grant/
 */
class TokenEndpointHandler implements RequestHandlerInterface
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

    private function createResponse(): ResponseInterface
    {
        return $this->responseFactory->createResponse();
    }

    /**
     * Request an access token
     *
     * Used for client credential grant, password grant, and refresh token grant
     *
     * @see https://oauth2.thephpleague.com/authorization-server/client-credentials-grant/
     * @see https://oauth2.thephpleague.com/authorization-server/resource-owner-password-credentials-grant/
     * @see https://oauth2.thephpleague.com/authorization-server/refresh-token-grant/
     * @see https://tools.ietf.org/html/rfc6749#section-3.2
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $response = $this->createResponse();

        try {
            return $this->server->respondToAccessTokenRequest($request, $response);
        } catch (OAuthServerException $exception) {
            return $exception->generateHttpResponse($response);
        }
    }
}
