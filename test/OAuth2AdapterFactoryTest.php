<?php

/**
 * @see       https://github.com/mezzio/mezzio-authentication-oauth2 for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-authentication-oauth2/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-authentication-oauth2/blob/master/LICENSE.md New BSD License
 */

namespace MezzioTest\Authentication\OAuth2;

use League\OAuth2\Server\ResourceServer;
use Mezzio\Authentication\AuthenticationInterface;
use Mezzio\Authentication\OAuth2\OAuth2Adapter;
use Mezzio\Authentication\OAuth2\OAuth2AdapterFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;

class OAuth2AdapterFactoryTest extends TestCase
{
    public function setUp()
    {
        $this->container      = $this->prophesize(ContainerInterface::class);
        $this->resourceServer = $this->prophesize(ResourceServer::class);
        $this->response       = $this->prophesize(ResponseInterface::class);
    }

    public function testConstructor()
    {
        $factory = new OAuth2AdapterFactory();
        $this->assertInstanceOf(OAuth2AdapterFactory::class, $factory);
    }

    /**
     * @expectedException Mezzio\Authentication\OAuth2\Exception\InvalidConfigException
     */
    public function testInvokeWithEmptyContainer()
    {
        $factory = new OAuth2AdapterFactory();
        $oauth2Adapter = $factory($this->container->reveal());
    }

    public function testInvokeWithResourceServerEmptyResponse()
    {
        $this->container->has(ResourceServer::class)
                        ->willReturn(true);
        $this->container->get(ResourceServer::class)
                        ->willReturn($this->resourceServer->reveal());

        $this->container->has(ResponseInterface::class)
                        ->willReturn(false);

        $factory = new OAuth2AdapterFactory();
        $adapter = $factory($this->container->reveal());

        $this->assertInstanceOf(OAuth2Adapter::class, $adapter);
        $this->assertInstanceOf(AuthenticationInterface::class, $adapter);
    }

    public function testInvokeResourceServerAndResponse()
    {
        $this->container->has(ResourceServer::class)
                        ->willReturn(true);
        $this->container->get(ResourceServer::class)
                        ->willReturn($this->resourceServer->reveal());

        $this->container->has(ResponseInterface::class)
                        ->willReturn(true);
        $this->container->get(ResponseInterface::class)
                        ->willReturn($this->response->reveal());

        $factory = new OAuth2AdapterFactory();
        $adapter = $factory($this->container->reveal());

        $this->assertInstanceOf(OAuth2Adapter::class, $adapter);
        $this->assertInstanceOf(AuthenticationInterface::class, $adapter);
    }
}
