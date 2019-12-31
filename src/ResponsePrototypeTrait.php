<?php

/**
 * @see       https://github.com/mezzio/mezzio-authentication-oauth2 for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-authentication-oauth2/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-authentication-oauth2/blob/master/LICENSE.md New BSD License
 */
namespace Mezzio\Authentication\OAuth2;

use Laminas\Diactoros\Response;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;

trait ResponsePrototypeTrait
{
    protected function getResponsePrototype(ContainerInterface $container) : ResponseInterface
    {
        // @codeCoverageIgnoreStart
        if (! $container->has(ResponseInterface::class)
            && ! class_exists(Response::class)
        ) {
            throw new Exception\InvalidConfigException(sprintf(
                'Cannot create %s service; dependency %s is missing. Either define the service, '
                . 'or install laminas/laminas-diactoros',
                OAuth2Middleware::class,
                ResponseInterface::class
            ));
        }
        // @codeCoverageIgnoreEnd

        return $container->has(ResponseInterface::class)
            ? $container->get(ResponseInterface::class)
            : new Response();
    }
}
