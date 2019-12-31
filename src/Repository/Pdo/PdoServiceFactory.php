<?php

/**
 * @see       https://github.com/mezzio/mezzio-authentication-oauth2 for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-authentication-oauth2/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-authentication-oauth2/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Mezzio\Authentication\OAuth2\Repository\Pdo;

use Mezzio\Authentication\OAuth2\Exception;
use Psr\Container\ContainerInterface;

class PdoServiceFactory
{
    public function __invoke(ContainerInterface $container) : PdoService
    {
        $config = $container->has('config') ? $container->get('config') : [];
        $config = $config['authentication']['pdo'] ?? null;
        if (null === $config) {
            throw new Exception\InvalidConfigException(
                'The PDO configuration is missing'
            );
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
