<?php

declare(strict_types=1);

namespace Mezzio\Authentication\OAuth2\Grant;

use League\OAuth2\Server\Grant\ClientCredentialsGrant;
use Psr\Container\ContainerInterface;

/** @final */
class ClientCredentialsGrantFactory
{
    public function __invoke(ContainerInterface $container): ClientCredentialsGrant
    {
        return new ClientCredentialsGrant();
    }
}
