<?php

declare(strict_types=1);

namespace MezzioTest\Authentication\OAuth2;

use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\RepositoryInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;
use Mezzio\Authentication\OAuth2\Exception;
use Mezzio\Authentication\OAuth2\RepositoryTrait;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class RepositoryTraitTest extends TestCase
{
    private object $trait;
    private InMemoryContainer $container;

    protected function setUp(): void
    {
        $this->trait     = new class {
            use RepositoryTrait;

            public function proxy(string $name, ContainerInterface $container): RepositoryInterface
            {
                return $this->$name($container);
            }
        };
        $this->container = new InMemoryContainer();
    }

    public function testGetUserRepositoryWithoutService(): void
    {
        $this->expectException(Exception\InvalidConfigException::class);
        $this->trait->proxy('getUserRepository', $this->container);
    }

    public function testGetUserRepository(): void
    {
        $this->container->set(UserRepositoryInterface::class, $this->createMock(UserRepositoryInterface::class));
        $result = $this->trait->proxy('getUserRepository', $this->container);
        self::assertInstanceOf(UserRepositoryInterface::class, $result);
    }

    public function testGetScopeRepositoryWithoutService(): void
    {
        $this->expectException(Exception\InvalidConfigException::class);
        $this->trait->proxy('getScopeRepository', $this->container);
    }

    public function testGetScopeRepository(): void
    {
        $this->container->set(ScopeRepositoryInterface::class, $this->createMock(ScopeRepositoryInterface::class));
        $result = $this->trait->proxy('getScopeRepository', $this->container);
        self::assertInstanceOf(ScopeRepositoryInterface::class, $result);
    }

    public function testGetAccessTokenRepositoryWithoutService(): void
    {
        $this->expectException(Exception\InvalidConfigException::class);
        $this->trait->proxy('getAccessTokenRepository', $this->container);
    }

    public function testGetAccessTokenRepository(): void
    {
        $this->container->set(
            AccessTokenRepositoryInterface::class,
            $this->createMock(AccessTokenRepositoryInterface::class)
        );

        $result = $this->trait->proxy('getAccessTokenRepository', $this->container);
        self::assertInstanceOf(AccessTokenRepositoryInterface::class, $result);
    }

    public function testGetClientRepositoryWithoutService(): void
    {
        $this->expectException(Exception\InvalidConfigException::class);
        $this->trait->proxy('getClientRepository', $this->container);
    }

    public function testGetClientRepository(): void
    {
        $this->container->set(
            ClientRepositoryInterface::class,
            $this->createMock(ClientRepositoryInterface::class)
        );

        $result = $this->trait->proxy('getClientRepository', $this->container);
        self::assertInstanceOf(ClientRepositoryInterface::class, $result);
    }

    public function testGetRefreshTokenRepositoryWithoutService(): void
    {
        $this->expectException(Exception\InvalidConfigException::class);
        $this->trait->proxy('getRefreshTokenRepository', $this->container);
    }

    public function testGetRefreshTokenRepository(): void
    {
        $this->container->set(
            RefreshTokenRepositoryInterface::class,
            $this->createMock(RefreshTokenRepositoryInterface::class),
        );

        $result = $this->trait->proxy('getRefreshTokenRepository', $this->container);
        self::assertInstanceOf(RefreshTokenRepositoryInterface::class, $result);
    }

    public function testGetAuthCodeRepositoryWithoutService(): void
    {
        $this->expectException(Exception\InvalidConfigException::class);
        $this->trait->proxy('getAuthCodeRepository', $this->container);
    }

    public function testGetAuthCodeRepository(): void
    {
        $this->container->set(
            AuthCodeRepositoryInterface::class,
            $this->createMock(AuthCodeRepositoryInterface::class),
        );

        $result = $this->trait->proxy('getAuthCodeRepository', $this->container);
        self::assertInstanceOf(AuthCodeRepositoryInterface::class, $result);
    }
}
