<?php

declare(strict_types=1);

namespace MezzioTest\Authentication\OAuth2;

use Laminas\ServiceManager\ServiceManager;
use League\OAuth2\Server\AuthorizationServer;
use Mezzio\Authentication\OAuth2\AuthorizationHandler;
use Mezzio\Authentication\OAuth2\AuthorizationHandlerFactory;
use Mezzio\Authentication\OAuth2\ConfigProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
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

    /** @var ResponseInterface&MockObject */
    private $response;

    protected function setUp(): void
    {
        $this->container = $this->prophesize(ContainerInterface::class);
        $this->container
            ->has(ResponseFactoryInterface::class)
            ->willReturn(false);
        $this->authServer = $this->prophesize(AuthorizationServer::class);
        $this->response   = $this->createMock(ResponseInterface::class);
    }

    public function testConstructor(): void
    {
        $factory = new AuthorizationHandlerFactory();
        $this->assertInstanceOf(AuthorizationHandlerFactory::class, $factory);
    }

    public function testRaisesTypeErrorForInvalidAuthorizationServer(): void
    {
        $this->container
            ->get(AuthorizationServer::class)
            ->willReturn(new stdClass());
        $this->container
            ->get(ResponseInterface::class)
            ->willReturn(static function (): void {
            });

        $factory = new AuthorizationHandlerFactory();

        $this->expectException(TypeError::class);
        $factory($this->container->reveal());
    }

    public function testFactoryRaisesTypeErrorForNonCallableResponseFactory(): void
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

    public function testFactoryRaisesTypeErrorWhenResponseServiceProvidesResponseInstance(): void
    {
        $this->container
            ->get(AuthorizationServer::class)
            ->willReturn($this->authServer->reveal());
        $this->container
            ->get(ResponseInterface::class)
            ->willReturn($this->response);

        $factory = new AuthorizationHandlerFactory();

        $this->expectException(TypeError::class);
        $factory($this->container->reveal());
    }

    public function testFactoryReturnsInstanceWhenAppropriateDependenciesArePresentInContainer(): void
    {
        $this->container
            ->get(AuthorizationServer::class)
            ->willReturn($this->authServer->reveal());
        $this->container
            ->get(ResponseInterface::class)
            ->willReturn(fn(): MockObject => $this->response)->shouldBeCalled();

        $factory = new AuthorizationHandlerFactory();
        $factory($this->container->reveal());
    }

    public function testConfigProvider(): void
    {
        $authServer      = $this->prophesize(AuthorizationServer::class)->reveal();
        $responseFactory = fn(): object => $this->prophesize(ResponseInterface::class)->reveal();

        $container = new ServiceManager((new ConfigProvider())->getDependencies());
        $container->setService(AuthorizationServer::class, $authServer);
        $container->setService(ResponseInterface::class, $responseFactory);

        $authHandler = $container->get(AuthorizationHandler::class);
        $this->assertInstanceOf(AuthorizationHandler::class, $authHandler);
    }
}
