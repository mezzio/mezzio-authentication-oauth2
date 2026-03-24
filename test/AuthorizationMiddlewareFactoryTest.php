<?php

declare(strict_types=1);

namespace MezzioTest\Authentication\OAuth2;

use League\OAuth2\Server\AuthorizationServer;
use Mezzio\Authentication\OAuth2\AuthorizationMiddlewareFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use stdClass;
use TypeError;

#[CoversClass(AuthorizationMiddlewareFactory::class)]
final class AuthorizationMiddlewareFactoryTest extends TestCase
{
    private MockObject&AuthorizationServer $authServer;

    private MockObject&ContainerInterface $container;

    private MockObject&ResponseInterface $response;

    private MockObject&LoggerInterface $logger;

    protected function setUp(): void
    {
        $this->container  = $this->createMock(ContainerInterface::class);
        $this->authServer = $this->createMock(AuthorizationServer::class);
        $this->response   = $this->createMock(ResponseInterface::class);
        $this->logger     = $this->createMock(LoggerInterface::class);
        $this->container->method('has')
            ->willReturnMap([
                [ResponseFactoryInterface::class, false],
                [LoggerInterface::class, true],
            ]);
    }

    public function testConstructor(): void
    {
        $factory = new AuthorizationMiddlewareFactory();
        self::assertInstanceOf(AuthorizationMiddlewareFactory::class, $factory);
    }

    public function testRaisesTypeErrorForInvalidAuthorizationServer(): void
    {
        $this->container->expects(self::exactly(3))
            ->method('get')
            ->willReturnMap([
                [AuthorizationServer::class, new stdClass()],
                [ResponseInterface::class, static fn () => null],
                [LoggerInterface::class, $this->logger],
            ]);

        $factory = new AuthorizationMiddlewareFactory();

        $this->expectException(TypeError::class);
        $factory($this->container);
    }

    public function testFactoryRaisesTypeErrorForNonCallableResponseFactory(): void
    {
        $this->container->expects(self::exactly(3))
            ->method('get')
            ->willReturnMap([
                [AuthorizationServer::class, $this->authServer],
                [ResponseInterface::class, new stdClass()],
                [LoggerInterface::class, $this->logger],
            ]);

        $factory = new AuthorizationMiddlewareFactory();

        $this->expectException(TypeError::class);
        $factory($this->container);
    }

    public function testFactoryRaisesTypeErrorWhenResponseServiceProvidesResponseInstance(): void
    {
        $this->container->expects(self::exactly(3))
            ->method('get')
            ->willReturnMap([
                [AuthorizationServer::class, $this->authServer],
                [ResponseInterface::class, $this->response],
                [LoggerInterface::class, $this->logger],
            ]);

        $factory = new AuthorizationMiddlewareFactory();

        $this->expectException(TypeError::class);
        $factory($this->container);
    }

    public function testFactoryReturnsInstanceWhenAppropriateDependenciesArePresentInContainer(): void
    {
        $this->container->expects(self::exactly(3))
            ->method('get')
            ->willReturnMap([
                [AuthorizationServer::class, $this->authServer],
                [ResponseInterface::class, fn (): ResponseInterface => $this->response],
                [LoggerInterface::class, $this->logger],
            ]);

        $factory = new AuthorizationMiddlewareFactory();
        $factory($this->container);
    }
}
