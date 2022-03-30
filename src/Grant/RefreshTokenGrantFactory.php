<?php

declare(strict_types=1);

namespace Mezzio\Authentication\OAuth2\Grant;

use DateInterval;
use League\OAuth2\Server\Grant\RefreshTokenGrant;
use Mezzio\Authentication\OAuth2\ConfigTrait;
use Mezzio\Authentication\OAuth2\RepositoryTrait;
use Psr\Container\ContainerInterface;

class RefreshTokenGrantFactory
{
    use ConfigTrait;

    use RepositoryTrait;

    public function __invoke(ContainerInterface $container): RefreshTokenGrant
    {
        $grant = new RefreshTokenGrant(
            $this->getRefreshTokenRepository($container)
        );

        $grant->setRefreshTokenTTL(
            new DateInterval($this->getRefreshTokenExpire($container))
        );

        return $grant;
    }
}
