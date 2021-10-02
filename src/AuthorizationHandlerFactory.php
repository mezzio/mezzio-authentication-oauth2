<?php

/**
 * @see       https://github.com/mezzio/mezzio-authentication-oauth2 for the canonical source repository
 */

declare(strict_types=1);

namespace Mezzio\Authentication\OAuth2;

use League\OAuth2\Server\AuthorizationServer;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;

final class AuthorizationHandlerFactory
{
    public function __invoke(ContainerInterface $container): AuthorizationHandler
    {
        return new AuthorizationHandler(
            $container->get(AuthorizationServer::class),
            $container->get(ResponseInterface::class)
        );
    }
}
