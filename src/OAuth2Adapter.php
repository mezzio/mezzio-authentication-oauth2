<?php

/**
 * @see       https://github.com/mezzio/mezzio-authentication-oauth2 for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-authentication-oauth2/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-authentication-oauth2/blob/master/LICENSE.md New BSD License
 */

namespace Mezzio\Authentication\OAuth2;

use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\ResourceServer;
use Mezzio\Authentication\AuthenticationInterface;
use Mezzio\Authentication\UserInterface;
use Mezzio\Authentication\UserRepository\UserTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class OAuth2Adapter implements AuthenticationInterface
{
    use UserTrait;

    /**
     * @var ResourceServer
     */
    protected $resourceServer;

    /**
     * @var ResponseInterface
     */
    protected $responsePrototype;

    /**
     * Constructor
     *
     * @param ResourceServer $resourceServer
     * @param ResponseInterface $responsePrototype
     */
    public function __construct(ResourceServer $resourceServer, ResponseInterface $responsePrototype)
    {
        $this->resourceServer = $resourceServer;
        $this->responsePrototype = $responsePrototype;
    }

    /**
     * {@inheritDoc}
     */
    public function authenticate(ServerRequestInterface $request) : ?UserInterface
    {
        try {
            $result = $this->resourceServer->validateAuthenticatedRequest($request);
            $userId = $result->getAttribute('oauth_user_id', false);
            if (false !== $userId) {
                return $this->generateUser($userId, '');
            }
        } catch (OAuthServerException $exception) {
            return null;
        }
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function unauthorizedResponse(ServerRequestInterface $request) : ResponseInterface
    {
        return $this->responsePrototype
            ->withHeader(
                'WWW-Authenticate',
                'Bearer token-example'
            )
            ->withStatus(401);
    }
}
