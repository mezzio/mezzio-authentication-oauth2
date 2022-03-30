<?php

declare(strict_types=1);

namespace Mezzio\Authentication\OAuth2;

use League\OAuth2\Server\AuthorizationServer;
use Psr\Container\ContainerInterface;

final class AuthorizationMiddlewareFactory
{
    use Psr17ResponseFactoryTrait;

    public function __invoke(ContainerInterface $container): AuthorizationMiddleware
    {
        return new AuthorizationMiddleware(
            $container->get(AuthorizationServer::class),
            $this->detectResponseFactory($container)
        );
    }
}
