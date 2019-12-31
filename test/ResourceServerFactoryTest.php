<?php

/**
 * @see       https://github.com/mezzio/mezzio-authentication-oauth2 for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-authentication-oauth2/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-authentication-oauth2/blob/master/LICENSE.md New BSD License
 */

namespace MezzioTest\Authentication\OAuth2;

use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use League\OAuth2\Server\ResourceServer;
use Mezzio\Authentication\OAuth2\Exception;
use Mezzio\Authentication\OAuth2\ResourceServerFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class ResourceServerFactoryTest extends TestCase
{
    const PUBLIC_KEY = __DIR__ . '/TestAsset/public.key';

    public function setUp()
    {
        $this->container  = $this->prophesize(ContainerInterface::class);
    }

    public function testConstructor()
    {
        $factory = new ResourceServerFactory();
        $this->assertInstanceOf(ResourceServerFactory::class, $factory);
    }

    public function testInvokeWithEmptyConfig()
    {
        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn([]);
        $factory = new ResourceServerFactory();

        $this->expectException(Exception\InvalidConfigException::class);
        $resourceServer = $factory($this->container->reveal());
    }

    /**
     * @expectedException Mezzio\Authentication\OAuth2\Exception\InvalidConfigException
     */
    public function testInvokeWithConfigWithoutRepository()
    {
        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn([
            'authentication' => [
                'public_key' => self::PUBLIC_KEY
            ]
        ]);
        $this->container
            ->has(AccessTokenRepositoryInterface::class)
            ->willReturn(false);

        $factory = new ResourceServerFactory();
        $resourceServer = $factory($this->container->reveal());
    }

    public function testInvokeWithConfigAndRepository()
    {
        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn([
            'authentication' => [
                'public_key' => self::PUBLIC_KEY
            ]
        ]);
        $this->container
            ->has(AccessTokenRepositoryInterface::class)
            ->willReturn(true);
        $this->container
            ->get(AccessTokenRepositoryInterface::class)
            ->willReturn(
                $this->prophesize(AccessTokenRepositoryInterface::class)->reveal()
            );

        $factory = new ResourceServerFactory();
        $resourceServer = $factory($this->container->reveal());
        $this->assertInstanceOf(ResourceServer::class, $resourceServer);
    }
}
