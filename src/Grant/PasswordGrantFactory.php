<?php

declare(strict_types=1);

namespace Mezzio\Authentication\OAuth2\Grant;

use DateInterval;
use League\OAuth2\Server\Grant\PasswordGrant;
use Mezzio\Authentication\OAuth2\ConfigTrait;
use Mezzio\Authentication\OAuth2\RepositoryTrait;
use Psr\Container\ContainerInterface;

class PasswordGrantFactory
{
    use ConfigTrait;
    use RepositoryTrait;

    public function __invoke(ContainerInterface $container): PasswordGrant
    {
        $grant = new PasswordGrant(
            $this->getUserRepository($container),
            $this->getRefreshTokenRepository($container)
        );

        $grant->setRefreshTokenTTL(
            new DateInterval($this->getRefreshTokenExpire($container))
        );

        return $grant;
    }
}
