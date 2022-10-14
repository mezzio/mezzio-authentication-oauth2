<?php

declare(strict_types=1);

namespace MezzioTest\Authentication\OAuth2;

use League\OAuth2\Server\AuthorizationServer;
use Mezzio\Authentication\OAuth2\AuthorizationMiddlewareFactory;
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
 * @covers \Mezzio\Authentication\OAuth2\AuthorizationMiddlewareFactory
 */
class AuthorizationMiddlewareFactoryTest extends TestCase
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
        $this->container  = $this->prophesize(ContainerInterface::class);
        $this->authServer = $this->prophesize(AuthorizationServer::class);
        $this->response   = $this->createMock(ResponseInterface::class);
        $this->container
            ->has(ResponseFactoryInterface::class)
            ->willReturn(false);
    }

    public function testConstructor(): void
    {
        $factory = new AuthorizationMiddlewareFactory();
        $this->assertInstanceOf(AuthorizationMiddlewareFactory::class, $factory);
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

        $factory = new AuthorizationMiddlewareFactory();

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

        $factory = new AuthorizationMiddlewareFactory();

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

        $factory = new AuthorizationMiddlewareFactory();

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
            ->willReturn(fn(): MockObject => $this->response)
            ->shouldBeCalled();

        $factory = new AuthorizationMiddlewareFactory();
        $factory($this->container->reveal());
    }
}
