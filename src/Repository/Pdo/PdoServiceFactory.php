<?php

declare(strict_types=1);

namespace Mezzio\Authentication\OAuth2\Repository\Pdo;

use Mezzio\Authentication\OAuth2\Exception;
use PDO;
use Psr\Container\ContainerInterface;

use function is_string;

class PdoServiceFactory
{
    public function __invoke(ContainerInterface $container): PDO
    {
        $config = $container->has('config') ? $container->get('config') : [];
        $config = $config['authentication']['pdo'] ?? null;
        if (null === $config) {
            throw new Exception\InvalidConfigException(
                'The PDO configuration is missing'
            );
        }

        if (is_string($config) && ! $container->has($config)) {
            throw new Exception\InvalidConfigException(
                'Invalid service for PDO'
            );
        }

        if (is_string($config) && $container->has($config)) {
            return $container->get($config);
        }

        if (! isset($config['dsn'])) {
            throw new Exception\InvalidConfigException(
                'The DSN configuration is missing for PDO'
            );
        }
        $username = $config['username'] ?? null;
        $password = $config['password'] ?? null;
        return new PdoService($config['dsn'], $username, $password);
    }
}
