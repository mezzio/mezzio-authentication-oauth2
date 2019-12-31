<?php

/**
 * @see       https://github.com/mezzio/mezzio-authentication-oauth2 for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-authentication-oauth2/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-authentication-oauth2/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Mezzio\Authentication\OAuth2;

use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\RequestTypes\AuthorizationRequest;
use phpDocumentor\Reflection\Types\This;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

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
    /**
     * @var AuthorizationServer
     */
    private $server;

    /**
     * @var callable
     */
    private $responseFactory;

    public function __construct(AuthorizationServer $server, callable $responseFactory)
    {
        $this->server = $server;
        $this->responseFactory = function () use ($responseFactory): ResponseInterface {
            return $responseFactory();
        };
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $authRequest = $request->getAttribute(AuthorizationRequest::class);
        return $this->server->completeAuthorizationRequest($authRequest, ($this->responseFactory)());
    }
}
