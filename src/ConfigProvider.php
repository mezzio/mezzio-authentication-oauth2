<?php

declare(strict_types=1);

namespace Mezzio\Authentication\OAuth2;

use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Grant\AuthCodeGrant;
use League\OAuth2\Server\Grant\ClientCredentialsGrant;
use League\OAuth2\Server\Grant\ImplicitGrant;
use League\OAuth2\Server\Grant\PasswordGrant;
use League\OAuth2\Server\Grant\RefreshTokenGrant;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;
use League\OAuth2\Server\ResourceServer;
use Mezzio\Authentication\AuthenticationInterface;
use Mezzio\Authentication\OAuth2\Grant\AuthCodeGrantFactory;
use Mezzio\Authentication\OAuth2\Grant\ClientCredentialsGrantFactory;
use Mezzio\Authentication\OAuth2\Grant\ImplicitGrantFactory;
use Mezzio\Authentication\OAuth2\Grant\PasswordGrantFactory;
use Mezzio\Authentication\OAuth2\Grant\RefreshTokenGrantFactory;
use Mezzio\Authentication\OAuth2\Repository\Pdo;

/**
 * @codeCoverageIgnore
 */
class ConfigProvider
{
    /**
     * Return the configuration array.
     */
    public function __invoke(): array
    {
        return [
            'dependencies'   => $this->getDependencies(),
            'authentication' => include __DIR__ . '/../config/oauth2.php',
            'routes'         => $this->getRoutes(),
        ];
    }

    /**
     * Returns the container dependencies
     */
    public function getDependencies(): array
    {
        return [
            'aliases'   => [
                // Choose a different adapter changing the alias value
                AccessTokenRepositoryInterface::class  => Pdo\AccessTokenRepository::class,
                AuthCodeRepositoryInterface::class     => Pdo\AuthCodeRepository::class,
                ClientRepositoryInterface::class       => Pdo\ClientRepository::class,
                RefreshTokenRepositoryInterface::class => Pdo\RefreshTokenRepository::class,
                ScopeRepositoryInterface::class        => Pdo\ScopeRepository::class,
                UserRepositoryInterface::class         => Pdo\UserRepository::class,
                AuthenticationInterface::class         => OAuth2Adapter::class,

                // Legacy Zend Framework aliases
                // @codingStandardsIgnoreStart
                \Zend\Expressive\Authentication\AuthenticationInterface::class => AuthenticationInterface::class,
                \Zend\Expressive\Authentication\OAuth2\AuthorizationMiddleware::class => AuthorizationMiddleware::class,
                \Zend\Expressive\Authentication\OAuth2\AuthorizationHandler::class => AuthorizationHandler::class,
                \Zend\Expressive\Authentication\OAuth2\TokenEndpointHandler::class => TokenEndpointHandler::class,
                \Zend\Expressive\Authentication\OAuth2\OAuth2Adapter::class => OAuth2Adapter::class,
                \Zend\Expressive\Authentication\OAuth2\Repository\Pdo\PdoService::class => Pdo\PdoService::class,
                \Zend\Expressive\Authentication\OAuth2\Repository\Pdo\AccessTokenRepository::class => Pdo\AccessTokenRepository::class,
                \Zend\Expressive\Authentication\OAuth2\Repository\Pdo\AuthCodeRepository::class => Pdo\AuthCodeRepository::class,
                \Zend\Expressive\Authentication\OAuth2\Repository\Pdo\ClientRepository::class => Pdo\ClientRepository::class,
                \Zend\Expressive\Authentication\OAuth2\Repository\Pdo\RefreshTokenRepository::class => Pdo\RefreshTokenRepository::class,
                \Zend\Expressive\Authentication\OAuth2\Repository\Pdo\ScopeRepository::class => Pdo\ScopeRepository::class,
                \Zend\Expressive\Authentication\OAuth2\Repository\Pdo\UserRepository::class => Pdo\UserRepository::class,
                \Zend\Expressive\Authentication\OAuth2\PasswordGrant::class => PasswordGrant::class,
                // @codingStandardsIgnoreEnd
            ],
            'factories' => [
                AuthorizationMiddleware::class => AuthorizationMiddlewareFactory::class,
                AuthorizationHandler::class    => AuthorizationHandlerFactory::class,
                TokenEndpointHandler::class    => TokenEndpointHandlerFactory::class,
                OAuth2Adapter::class           => OAuth2AdapterFactory::class,
                AuthorizationServer::class     => AuthorizationServerFactory::class,
                ResourceServer::class          => ResourceServerFactory::class,
                // Pdo adapter
                Pdo\PdoService::class             => Pdo\PdoServiceFactory::class,
                Pdo\AccessTokenRepository::class  => Pdo\AccessTokenRepositoryFactory::class,
                Pdo\AuthCodeRepository::class     => Pdo\AuthCodeRepositoryFactory::class,
                Pdo\ClientRepository::class       => Pdo\ClientRepositoryFactory::class,
                Pdo\RefreshTokenRepository::class => Pdo\RefreshTokenRepositoryFactory::class,
                Pdo\ScopeRepository::class        => Pdo\ScopeRepositoryFactory::class,
                Pdo\UserRepository::class         => Pdo\UserRepositoryFactory::class,
                // Default Grants
                ClientCredentialsGrant::class => ClientCredentialsGrantFactory::class,
                PasswordGrant::class          => PasswordGrantFactory::class,
                AuthCodeGrant::class          => AuthCodeGrantFactory::class,
                ImplicitGrant::class          => ImplicitGrantFactory::class,
                RefreshTokenGrant::class      => RefreshTokenGrantFactory::class,
            ],
        ];
    }

    public function getRoutes(): array
    {
        return [
            [
                'name'            => 'oauth',
                'path'            => '/oauth',
                'middleware'      => AuthorizationMiddleware::class,
                'allowed_methods' => ['GET', 'POST'],
            ],
        ];
    }
}
