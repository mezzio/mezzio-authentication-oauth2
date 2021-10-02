<?php

declare(strict_types=1);

namespace Mezzio\Authentication\OAuth2;

use League\OAuth2\Server\ResourceServer;
use Psr\Container\ContainerInterface;

class ResourceServerFactory
{
    use CryptKeyTrait;
    use RepositoryTrait;

    public function __invoke(ContainerInterface $container): ResourceServer
    {
        $config = $container->has('config') ? $container->get('config') : [];
        $config = $config['authentication'] ?? [];

        if (! isset($config['public_key'])) {
            throw new Exception\InvalidConfigException(
                'The public_key value is missing in config authentication'
            );
        }

        $publicKey = $this->getCryptKey($config['public_key'], 'authentication.public_key');

        return new ResourceServer(
            $this->getAccessTokenRepository($container),
            $publicKey
        );
    }
}
