<?php

/**
 * @see       https://github.com/mezzio/mezzio-authentication-oauth2 for the canonical source repository
 */

declare(strict_types=1);

namespace Mezzio\Authentication\OAuth2\Repository\Pdo;

use Psr\Container\ContainerInterface;

class AuthCodeRepositoryFactory
{
    public function __invoke(ContainerInterface $container): AuthCodeRepository
    {
        return new AuthCodeRepository(
            $container->get(PdoService::class)
        );
    }
}
