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
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use stdClass;
use TypeError;

/**
 * @covers \Mezzio\Authentication\OAuth2\OAuth2MiddlewareFactory
 */
class OAuth2MiddlewareFactoryTest extends TestCase
{
    /** @var AuthorizationServer|ObjectProphecy */
    private $authServer;

    /** @var AuthServer|ObjectProphecy */
    private $container;

    /** @var ResponseInterface|ObjectProphecy */
    private $response;

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

    public function testFactoryRaisesTypeErrorForNonCallableResponseFactory()
    {
        $this->container
            ->has(AuthorizationServer::class)
            ->willReturn(true);
        $this->container
            ->get(AuthorizationServer::class)
            ->willReturn($this->authServer->reveal());
        $this->container
            ->get(ResponseInterface::class)
            ->willReturn(new stdClass());

        $factory = new OAuth2MiddlewareFactory();

        $this->expectException(TypeError::class);
        $factory($this->container->reveal());
    }

    public function testFactoryRaisesTypeErrorWhenResponseServiceProvidesResponseInstance()
    {
        $this->container
            ->has(AuthorizationServer::class)
            ->willReturn(true);
        $this->container
            ->get(AuthorizationServer::class)
            ->willReturn($this->authServer->reveal());
        $this->container
            ->get(ResponseInterface::class)
            ->will([$this->response, 'reveal']);

        $factory = new OAuth2MiddlewareFactory();

        $this->expectException(TypeError::class);
        $factory($this->container->reveal());
    }

    public function testFactoryReturnsInstanceWhenAppropriateDependenciesArePresentInContainer()
    {
        $this->container
            ->has(AuthorizationServer::class)
            ->willReturn(true);
        $this->container
            ->get(AuthorizationServer::class)
            ->willReturn($this->authServer->reveal());
        $this->container
            ->get(ResponseInterface::class)
            ->willReturn(function () {
                return $this->response->reveal();
            });

        $factory = new OAuth2MiddlewareFactory();
        $middleware = $factory($this->container->reveal());
        $this->assertInstanceOf(OAuth2Middleware::class, $middleware);
    }
}
