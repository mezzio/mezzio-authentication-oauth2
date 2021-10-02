<?php

declare(strict_types=1);

namespace Mezzio\Authentication\OAuth2\Repository\Pdo;

use Psr\Container\ContainerInterface;

class UserRepositoryFactory
{
    public function __invoke(ContainerInterface $container): UserRepository
    {
        return new UserRepository(
            $container->get(PdoService::class)
        );
    }
}
