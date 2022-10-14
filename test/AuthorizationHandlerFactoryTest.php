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
    /** @var AuthorizationServer&MockObject */
    private AuthorizationServer $authServer;

    /** @var ContainerInterface&MockObject */
    private ContainerInterface $container;

    /** @var ResponseInterface&MockObject */
    private ResponseInterface $response;

    protected function setUp(): void
    {
        $this->container = $this->createMock(ContainerInterface::class);
        $this->container
            ->method('has')
            ->with(ResponseFactoryInterface::class)
            ->willReturn(false);
        $this->authServer = $this->createMock(AuthorizationServer::class);
        $this->response   = $this->createMock(ResponseInterface::class);
    }

    public function testConstructor(): void
    {
        $factory = new AuthorizationHandlerFactory();
        self::assertInstanceOf(AuthorizationHandlerFactory::class, $factory);
    }

    public function testRaisesTypeErrorForInvalidAuthorizationServer(): void
    {
        $this->container->expects(self::exactly(2))
            ->method('get')
            ->willReturnMap([
                [AuthorizationServer::class, new stdClass()],
                [ResponseInterface::class, fn () => null],
            ]);

        $factory = new AuthorizationHandlerFactory();

        $this->expectException(TypeError::class);
        $factory($this->container);
    }

    public function testFactoryRaisesTypeErrorForNonCallableResponseFactory(): void
    {
        $this->container->expects(self::exactly(2))
            ->method('get')
            ->willReturnMap([
                [AuthorizationServer::class, $this->authServer],
                [ResponseInterface::class, new stdClass()],
            ]);

        $factory = new AuthorizationHandlerFactory();

        $this->expectException(TypeError::class);
        $factory($this->container);
    }

    public function testFactoryRaisesTypeErrorWhenResponseServiceProvidesResponseInstance(): void
    {
        $this->container->expects(self::exactly(2))
            ->method('get')
            ->willReturnMap([
                [AuthorizationServer::class, $this->authServer],
                [ResponseInterface::class, $this->response],
            ]);

        $factory = new AuthorizationHandlerFactory();

        $this->expectException(TypeError::class);
        $factory($this->container);
    }

    public function testFactoryReturnsInstanceWhenAppropriateDependenciesArePresentInContainer(): void
    {
        $this->container->expects(self::exactly(2))
            ->method('get')
            ->willReturnMap([
                [AuthorizationServer::class, $this->authServer],
                [ResponseInterface::class, fn (): ResponseInterface => $this->response],
            ]);

        $factory = new AuthorizationHandlerFactory();
        $factory($this->container);
    }

    public function testConfigProvider(): void
    {
        $responseFactory = fn(): ResponseInterface => $this->response;

        $container = new ServiceManager((new ConfigProvider())->getDependencies());
        $container->setService(AuthorizationServer::class, $this->authServer);
        $container->setService(ResponseInterface::class, $responseFactory);

        $authHandler = $container->get(AuthorizationHandler::class);
        self::assertInstanceOf(AuthorizationHandler::class, $authHandler);
    }
}
