<?php

/**
 * @see       https://github.com/mezzio/mezzio-authentication-oauth2 for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-authentication-oauth2/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-authentication-oauth2/blob/master/LICENSE.md New BSD License
 */

namespace Mezzio\Authentication\OAuth2;

use League\OAuth2\Server\ResourceServer;
use Mezzio\Authentication\OAuth2\Exception;
use Mezzio\Authentication\ResponsePrototypeTrait;
use Psr\Container\ContainerInterface;

class OAuth2AdapterFactory
{
    use ResponsePrototypeTrait;

    public function __invoke(ContainerInterface $container) : OAuth2Adapter
    {
        $resourceServer = $container->has(ResourceServer::class)
            ? $container->get(ResourceServer::class)
            : null;

        if (null === $resourceServer) {
            throw new Exception\InvalidConfigException(
                'OAuth2 resource server is missing for authentication'
            );
        }

        return new OAuth2Adapter(
            $resourceServer,
            $this->getResponsePrototype($container)
        );
    }
}
