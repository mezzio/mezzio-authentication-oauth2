<?php

/**
 * @see       https://github.com/mezzio/mezzio-authentication-oauth2 for the canonical source repository
 */

declare(strict_types=1);

namespace Mezzio\Authentication\OAuth2\Repository\Pdo;

use Psr\Container\ContainerInterface;

class ScopeRepositoryFactory
{
    public function __invoke(ContainerInterface $container): ScopeRepository
    {
        return new ScopeRepository(
            $container->get(PdoService::class)
        );
    }
}
