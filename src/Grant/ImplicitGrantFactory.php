<?php

/**
 * @see       https://github.com/mezzio/mezzio-authentication-oauth2 for the canonical source repository
 */

declare(strict_types=1);

namespace Mezzio\Authentication\OAuth2\Grant;

use DateInterval;
use League\OAuth2\Server\Grant\ImplicitGrant;
use Mezzio\Authentication\OAuth2\ConfigTrait;
use Psr\Container\ContainerInterface;

class ImplicitGrantFactory
{
    use ConfigTrait;

    public function __invoke(ContainerInterface $container)
    {
        return new ImplicitGrant(
            new DateInterval($this->getAuthCodeExpire($container))
        );
    }
}
