<?php

/**
 * @see       https://github.com/mezzio/mezzio-authentication-oauth2 for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-authentication-oauth2/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-authentication-oauth2/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Mezzio\Authentication\OAuth2\Grant;

use League\OAuth2\Server\Grant\PasswordGrant;
use Mezzio\Authentication\OAuth2\ConfigTrait;
use Mezzio\Authentication\OAuth2\RepositoryTrait;
use Psr\Container\ContainerInterface;

class PasswordGrantFactory
{
    use RepositoryTrait;

    use ConfigTrait;

    public function __invoke(ContainerInterface $container)
    {
        $grant = new PasswordGrant(
            $this->getUserRepository($container),
            $this->getRefreshTokenRepository($container)
        );

        $grant->setRefreshTokenTTL(
            new \DateInterval($this->getRefreshTokenExpire($container))
        );

        return $grant;
    }
}
