<?php

/**
 * @see       https://github.com/mezzio/mezzio-authentication-oauth2 for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-authentication-oauth2/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-authentication-oauth2/blob/master/LICENSE.md New BSD License
 */

namespace Mezzio\Authentication\OAuth2;

use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;
use League\OAuth2\Server\ResourceServer;
use Mezzio\Authentication\OAuth2\Repository\Pdo;

class ConfigProvider
{
    /**
     * Return the configuration array.
     */
    public function __invoke() : array
    {
        return [
            'dependencies'   => $this->getDependencies(),
            'authentication' => include __DIR__ . '/../config/oauth2.php',
            'routes'         => $this->getRoutes()
        ];
    }

    /**
     * Returns the container dependencies
     */
    public function getDependencies() : array
    {
        return [
            'aliases' => [
                // Choose a different adapter changing the alias value
                AccessTokenRepositoryInterface::class => Pdo\AccessTokenRepository,
                AuthCodeEntityInterface::class => Pdo\AuthCodeRepository,
                ClientRepositoryInterface::class => Pdo\ClientRepository,
                RefreshTokenRepositoryInterface::class => Pdo\RefreshTokenRepository,
                ScopeRepositoryInterface::class => Pdo\ScopeRepository,
                UserRepositoryInterface::class => Pdo\UserRepository,

                // Legacy Zend Framework aliases
                \Zend\Expressive\Authentication\OAuth2\AuthCodeEntityInterface::class => AuthCodeEntityInterface::class,
                \Zend\Expressive\Authentication\OAuth2\UserRepositoryInterface::class => UserRepositoryInterface::class,
                \Zend\Expressive\Authentication\OAuth2\OAuth2Middleware::class => OAuth2Middleware::class,
                \Zend\Expressive\Authentication\OAuth2\OAuth2Adapter::class => OAuth2Adapter::class,
                \Zend\Expressive\Authentication\OAuth2\Repository\Pdo\PdoService::class => Pdo\PdoService::class,
                \Zend\Expressive\Authentication\OAuth2\Repository\Pdo\AccessTokenRepository::class => Pdo\AccessTokenRepository::class,
                \Zend\Expressive\Authentication\OAuth2\Repository\Pdo\AuthCodeRepository::class => Pdo\AuthCodeRepository::class,
                \Zend\Expressive\Authentication\OAuth2\Repository\Pdo\ClientRepository::class => Pdo\ClientRepository::class,
                \Zend\Expressive\Authentication\OAuth2\Repository\Pdo\RefreshTokenRepository::class => Pdo\RefreshTokenRepository::class,
                \Zend\Expressive\Authentication\OAuth2\Repository\Pdo\ScopeRepository::class => Pdo\ScopeRepository::class,
                \Zend\Expressive\Authentication\OAuth2\Repository\Pdo\UserRepository::class => Pdo\UserRepository::class,
            ],
            'factories' => [
                OAuth2Middleware::class => OAuth2MiddlewareFactory::class,
                OAuth2Adapter::class => OAuth2AdapterFactory::class,
                AuthorizationServer::class => AuthorizationServerFactory::class,
                ResourceServer::class => ResourceServerFactory::class,
                // Pdo adapter
                Pdo\PdoService::class => Pdo\PdoServiceFactory::class,
                Pdo\AccessTokenRepository::class => Pdo\AccessTokenRepositoryFactory::class,
                Pdo\AuthCodeRepository::class => Pdo\AuthCodeRepositoryFactory::class,
                Pdo\ClientRepository::class => Pdo\ClientRepositoryFactory::class,
                Pdo\RefreshTokenRepository::class => Pdo\RefreshTokenRepositoryFactory::class,
                Pdo\ScopeRepository::class => Pdo\ScopeRepositoryFactory::class,
                Pdo\UserRepository::class => Pdo\UserRepositoryFactory::class
            ]
        ];
    }

    public function getRoutes() : array
    {
        return [
            [
                'name'            => 'oauth',
                'path'            => '/oauth',
                'middleware'      => OAuth2Middleware::class,
                'allowed_methods' => ['GET', 'POST']
            ],
        ];
    }
}
