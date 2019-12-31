<?php

/**
 * @see       https://github.com/mezzio/mezzio-authentication-oauth2 for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-authentication-oauth2/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-authentication-oauth2/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace MezzioTest\Authentication\OAuth2;

use League\OAuth2\Server\AuthorizationServer;
use Mezzio\Authentication\OAuth2\Exception\InvalidConfigException;
use Mezzio\Authentication\OAuth2\OAuth2Middleware;
use Mezzio\Authentication\OAuth2\OAuth2MiddlewareFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * @covers Mezzio\Authentication\OAuth2\OAuth2MiddlewareFactory
 */
class OAuth2MiddlewareFactoryTest extends TestCase
{
    public function setUp()
    {
        $this->container  = $this->prophesize(ContainerInterface::class);
        $this->authServer = $this->prophesize(AuthorizationServer::class);
        $this->response   = $this->prophesize(ResponseInterface::class);
    }

    public function testConstructor()
    {
        $factory = new OAuth2MiddlewareFactory();
        $this->assertInstanceOf(OAuth2MiddlewareFactory::class, $factory);
    }

    public function testInvokeWithEmptyContainer()
    {
        $factory = new OAuth2MiddlewareFactory();

        $this->expectException(InvalidConfigException::class);
        $middleware = $factory($this->container->reveal());
    }

    /**
     * @covers Mezzio\Authentication\OAuth2\ResponsePrototypeTrait::getResponsePrototype
     */
    public function testInvokeWithAuthServerWithoutResponseInterface()
    {
        $this->container
            ->has(AuthorizationServer::class)
            ->willReturn(true);
        $this->container
            ->get(AuthorizationServer::class)
            ->willReturn($this->authServer->reveal());
        $this->container
            ->has(ResponseInterface::class)
            ->willReturn(false);

        $factory = new OAuth2MiddlewareFactory();
        $middleware = $factory($this->container->reveal());
        $this->assertInstanceOf(OAuth2Middleware::class, $middleware);
    }

    /**
     * @covers Mezzio\Authentication\OAuth2\ResponsePrototypeTrait::getResponsePrototype
     */
    public function testInvokeWithAuthServerWithResponseInterface()
    {
        $this->container
            ->has(AuthorizationServer::class)
            ->willReturn(true);
        $this->container
            ->has(ResponseInterface::class)
            ->willReturn(true);
        $this->container
            ->get(AuthorizationServer::class)
            ->willReturn($this->authServer->reveal());
        $this->container
            ->get(ResponseInterface::class)
            ->willReturn($this->response->reveal());

        $factory = new OAuth2MiddlewareFactory();
        $middleware = $factory($this->container->reveal());
        $this->assertInstanceOf(OAuth2Middleware::class, $middleware);
    }
}
