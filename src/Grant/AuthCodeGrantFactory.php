<?php

/**
 * @see       https://github.com/mezzio/mezzio-authentication-oauth2 for the canonical source repository
 */

declare(strict_types=1);

namespace Mezzio\Authentication\OAuth2\Grant;

use DateInterval;
use League\OAuth2\Server\Grant\AuthCodeGrant;
use Mezzio\Authentication\OAuth2\ConfigTrait;
use Mezzio\Authentication\OAuth2\RepositoryTrait;
use Psr\Container\ContainerInterface;

class AuthCodeGrantFactory
{
    use ConfigTrait;
    use RepositoryTrait;

    public function __invoke(ContainerInterface $container)
    {
        $grant = new AuthCodeGrant(
            $this->getAuthCodeRepository($container),
            $this->getRefreshTokenRepository($container),
            new DateInterval($this->getAuthCodeExpire($container))
        );

        $grant->setRefreshTokenTTL(
            new DateInterval($this->getRefreshTokenExpire($container))
        );

        return $grant;
    }
}
