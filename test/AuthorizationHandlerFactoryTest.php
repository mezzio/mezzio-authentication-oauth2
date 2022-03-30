<?php

declare(strict_types=1);

namespace MezzioTest\Authentication\OAuth2;

use Laminas\ServiceManager\ServiceManager;
use League\OAuth2\Server\AuthorizationServer;
use Mezzio\Authentication\OAuth2\AuthorizationHandler;
use Mezzio\Authentication\OAuth2\AuthorizationHandlerFactory;
use Mezzio\Authentication\OAuth2\ConfigProvider;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use stdClass;
use TypeError;

/**
 * @covers \Mezzio\Authentication\OAuth2\AuthorizationHandlerFactory
 */
class AuthorizationHandlerFactoryTest extends TestCase
{
    use ProphecyTrait;

    /** @var AuthorizationServer|ObjectProphecy */
    private $authServer;

    /** @var ContainerInterface|ObjectProphecy */
    private $container;

    /** @var ResponseInterface|ObjectProphecy */
    private $response;

    protected function setUp(): void
    {
        $this->container  = $this->prophesize(ContainerInterface::class);
        $this->authServer = $this->prophesize(AuthorizationServer::class);
        $this->response   = $this->prophesize(ResponseInterface::class);
    }

    public function testConstructor()
    {
        $factory = new AuthorizationHandlerFactory();
        $this->assertInstanceOf(AuthorizationHandlerFactory::class, $factory);
    }

    public function testRaisesTypeErrorForInvalidAuthorizationServer()
    {
        $this->container
            ->get(AuthorizationServer::class)
            ->willReturn(new stdClass());
        $this->container
            ->get(ResponseInterface::class)
            ->willReturn(function () {
            });

        $factory = new AuthorizationHandlerFactory();

        $this->expectException(TypeError::class);
        $factory($this->container->reveal());
    }

    public function testFactoryRaisesTypeErrorForNonCallableResponseFactory()
    {
        $this->container
            ->get(AuthorizationServer::class)
            ->willReturn($this->authServer->reveal());
        $this->container
            ->get(ResponseInterface::class)
            ->willReturn(new stdClass());

        $factory = new AuthorizationHandlerFactory();

        $this->expectException(TypeError::class);
        $factory($this->container->reveal());
    }

    public function testFactoryRaisesTypeErrorWhenResponseServiceProvidesResponseInstance()
    {
        $this->container
            ->get(AuthorizationServer::class)
            ->willReturn($this->authServer->reveal());
        $this->container
            ->get(ResponseInterface::class)
            ->will([$this->response, 'reveal']);

        $factory = new AuthorizationHandlerFactory();

        $this->expectException(TypeError::class);
        $factory($this->container->reveal());
    }

    public function testFactoryReturnsInstanceWhenAppropriateDependenciesArePresentInContainer()
    {
        $this->container
            ->get(AuthorizationServer::class)
            ->willReturn($this->authServer->reveal());
        $this->container
            ->get(ResponseInterface::class)
            ->willReturn(function () {
                return $this->response->reveal();
            });

        $factory    = new AuthorizationHandlerFactory();
        $middleware = $factory($this->container->reveal());
        $this->assertInstanceOf(AuthorizationHandler::class, $middleware);
    }

    public function testConfigProvider()
    {
        $authServer      = $this->prophesize(AuthorizationServer::class)->reveal();
        $responseFactory = function () {
            return $this->prophesize(ResponseInterface::class)->reveal();
        };

        $container = new ServiceManager((new ConfigProvider())->getDependencies());
        $container->setService(AuthorizationServer::class, $authServer);
        $container->setService(ResponseInterface::class, $responseFactory);

        $authHandler = $container->get(AuthorizationHandler::class);
        $this->assertInstanceOf(AuthorizationHandler::class, $authHandler);
    }
}
