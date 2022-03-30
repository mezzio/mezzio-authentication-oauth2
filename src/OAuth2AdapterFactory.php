<?php

declare(strict_types=1);

namespace Mezzio\Authentication\OAuth2;

use League\OAuth2\Server\ResourceServer;
use Mezzio\Authentication\UserInterface;
use Psr\Container\ContainerInterface;

class OAuth2AdapterFactory
{
    use Psr17ResponseFactoryTrait;

    public function __invoke(ContainerInterface $container): OAuth2Adapter
    {
        $resourceServer = $container->has(ResourceServer::class)
            ? $container->get(ResourceServer::class)
            : null;

        if (null === $resourceServer) {
            throw new Exception\InvalidConfigException(
                'OAuth2 resource server is missing for authentication'
            );
        }

        return new OAuth2Adapter(
            $resourceServer,
            $this->detectResponseFactory($container),
            $container->get(UserInterface::class)
        );
    }
}
