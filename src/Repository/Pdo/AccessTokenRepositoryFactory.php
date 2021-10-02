<?php

/**
 * @see       https://github.com/mezzio/mezzio-authentication-oauth2 for the canonical source repository
 */

declare(strict_types=1);

namespace Mezzio\Authentication\OAuth2\Repository\Pdo;

use Psr\Container\ContainerInterface;

class AccessTokenRepositoryFactory
{
    public function __invoke(ContainerInterface $container): AccessTokenRepository
    {
        return new AccessTokenRepository(
            $container->get(PdoService::class)
        );
    }
}
