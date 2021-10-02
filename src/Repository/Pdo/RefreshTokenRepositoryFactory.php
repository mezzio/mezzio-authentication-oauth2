<?php

declare(strict_types=1);

namespace Mezzio\Authentication\OAuth2\Repository\Pdo;

use Psr\Container\ContainerInterface;

class RefreshTokenRepositoryFactory
{
    public function __invoke(ContainerInterface $container): RefreshTokenRepository
    {
        return new RefreshTokenRepository(
            $container->get(PdoService::class)
        );
    }
}
