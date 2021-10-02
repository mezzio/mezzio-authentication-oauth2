<?php

/**
 * @see       https://github.com/mezzio/mezzio-authentication-oauth2 for the canonical source repository
 */

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
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;

class RepositoryTraitTest extends TestCase
{
    use ProphecyTrait;

    protected function setUp(): void
    {
        $this->trait     = new class {
            use RepositoryTrait;

            public function proxy(string $name, ContainerInterface $container): RepositoryInterface
            {
                return $this->$name($container);
            }
        };
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function testGetUserRepositoryWithoutService()
    {
        $this->container
            ->has(UserRepositoryInterface::class)
            ->willReturn(false);

        $this->expectException(Exception\InvalidConfigException::class);
        $this->trait->proxy('getUserRepository', $this->container->reveal());
    }

    public function testGetUserRepository()
    {
        $this->container
            ->has(UserRepositoryInterface::class)
            ->willReturn(true);
        $this->container
            ->get(UserRepositoryInterface::class)
            ->willReturn($this->prophesize(UserRepositoryInterface::class)->reveal());

        $result = $this->trait->proxy('getUserRepository', $this->container->reveal());
        $this->assertInstanceOf(UserRepositoryInterface::class, $result);
    }

    public function testGetScopeRepositoryWithoutService()
    {
        $this->container
            ->has(ScopeRepositoryInterface::class)
            ->willReturn(false);

        $this->expectException(Exception\InvalidConfigException::class);
        $this->trait->proxy('getScopeRepository', $this->container->reveal());
    }

    public function testGetScopeRepository()
    {
        $this->container
            ->has(ScopeRepositoryInterface::class)
            ->willReturn(true);
        $this->container
            ->get(ScopeRepositoryInterface::class)
            ->willReturn($this->prophesize(ScopeRepositoryInterface::class)->reveal());

        $result = $this->trait->proxy('getScopeRepository', $this->container->reveal());
        $this->assertInstanceOf(ScopeRepositoryInterface::class, $result);
    }

    public function testGetAccessTokenRepositoryWithoutService()
    {
        $this->container
            ->has(AccessTokenRepositoryInterface::class)
            ->willReturn(false);

        $this->expectException(Exception\InvalidConfigException::class);
        $this->trait->proxy('getAccessTokenRepository', $this->container->reveal());
    }

    public function testGetAccessTokenRepository()
    {
        $this->container
            ->has(AccessTokenRepositoryInterface::class)
            ->willReturn(true);
        $this->container
            ->get(AccessTokenRepositoryInterface::class)
            ->willReturn($this->prophesize(AccessTokenRepositoryInterface::class)->reveal());

        $result = $this->trait->proxy('getAccessTokenRepository', $this->container->reveal());
        $this->assertInstanceOf(AccessTokenRepositoryInterface::class, $result);
    }

    public function testGetClientRepositoryWithoutService()
    {
        $this->container
            ->has(ClientRepositoryInterface::class)
            ->willReturn(false);

        $this->expectException(Exception\InvalidConfigException::class);
        $this->trait->proxy('getClientRepository', $this->container->reveal());
    }

    public function testGetClientRepository()
    {
        $this->container
            ->has(ClientRepositoryInterface::class)
            ->willReturn(true);
        $this->container
            ->get(ClientRepositoryInterface::class)
            ->willReturn($this->prophesize(ClientRepositoryInterface::class)->reveal());

        $result = $this->trait->proxy('getClientRepository', $this->container->reveal());
        $this->assertInstanceOf(ClientRepositoryInterface::class, $result);
    }

    public function testGetRefreshTokenRepositoryWithoutService()
    {
        $this->container
            ->has(RefreshTokenRepositoryInterface::class)
            ->willReturn(false);

        $this->expectException(Exception\InvalidConfigException::class);
        $this->trait->proxy('getRefreshTokenRepository', $this->container->reveal());
    }

    public function testGetRefreshTokenRepository()
    {
        $this->container
            ->has(RefreshTokenRepositoryInterface::class)
            ->willReturn(true);
        $this->container
            ->get(RefreshTokenRepositoryInterface::class)
            ->willReturn($this->prophesize(RefreshTokenRepositoryInterface::class)->reveal());

        $result = $this->trait->proxy('getRefreshTokenRepository', $this->container->reveal());
        $this->assertInstanceOf(RefreshTokenRepositoryInterface::class, $result);
    }

    public function testGetAuthCodeRepositoryWithoutService()
    {
        $this->container
            ->has(AuthCodeRepositoryInterface::class)
            ->willReturn(false);

        $this->expectException(Exception\InvalidConfigException::class);
        $this->trait->proxy('getAuthCodeRepository', $this->container->reveal());
    }

    public function testGetAuthCodeRepository()
    {
        $this->container
            ->has(AuthCodeRepositoryInterface::class)
            ->willReturn(true);
        $this->container
            ->get(AuthCodeRepositoryInterface::class)
            ->willReturn($this->prophesize(AuthCodeRepositoryInterface::class)->reveal());

        $result = $this->trait->proxy('getAuthCodeRepository', $this->container->reveal());
        $this->assertInstanceOf(AuthCodeRepositoryInterface::class, $result);
    }
}
