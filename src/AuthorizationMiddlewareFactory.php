<?php

declare(strict_types=1);

namespace Mezzio\Authentication\OAuth2;

use League\OAuth2\Server\AuthorizationServer;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

use function assert;

final class AuthorizationMiddlewareFactory
{
    use Psr17ResponseFactoryTrait;

    public function __invoke(ContainerInterface $container): AuthorizationMiddleware
    {
        $logger = $container->has(LoggerInterface::class)
            ? $container->get(LoggerInterface::class)
            : null;

        assert($logger === null || $logger instanceof LoggerInterface);

        return new AuthorizationMiddleware(
            $container->get(AuthorizationServer::class),
            $this->detectResponseFactory($container),
            $logger
        );
    }
}
