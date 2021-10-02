<?php

declare(strict_types=1);

namespace Mezzio\Authentication\OAuth2;

use League\OAuth2\Server\AuthorizationServer;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;

final class TokenEndpointHandlerFactory
{
    public function __invoke(ContainerInterface $container): TokenEndpointHandler
    {
        return new TokenEndpointHandler(
            $container->get(AuthorizationServer::class),
            $container->get(ResponseInterface::class)
        );
    }
}
