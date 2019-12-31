<?php

/**
 * @see       https://github.com/mezzio/mezzio-authentication-oauth2 for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-authentication-oauth2/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-authentication-oauth2/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Mezzio\Authentication\OAuth2\Repository\Pdo;

use Psr\Container\ContainerInterface;

class RefreshTokenRepositoryFactory
{
    public function __invoke(ContainerInterface $container) : RefreshTokenRepository
    {
        return new RefreshTokenRepository(
            $container->get(PdoService::class)
        );
    }
}
