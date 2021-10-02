<?php

/**
 * @see       https://github.com/mezzio/mezzio-authentication-oauth2 for the canonical source repository
 */

declare(strict_types=1);

namespace Mezzio\Authentication\OAuth2\Repository\Pdo;

use Psr\Container\ContainerInterface;

class ClientRepositoryFactory
{
    public function __invoke(ContainerInterface $container): ClientRepository
    {
        return new ClientRepository(
            $container->get(PdoService::class)
        );
    }
}
