<?php

declare(strict_types=1);

namespace MezzioTest\Authentication\OAuth2;

use Closure;
use League\OAuth2\Server\ResourceServer;
use Mezzio\Authentication\AuthenticationInterface;
use Mezzio\Authentication\OAuth2\Exception;
use Mezzio\Authentication\OAuth2\OAuth2AdapterFactory;
use Mezzio\Authentication\UserInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use stdClass;
use TypeError;

class OAuth2AdapterFactoryTest extends TestCase
{
    use ProphecyTrait;

    /** @var ContainerInterface|ObjectProphecy */
    private $container;

    /** @var ResourceServer|ObjectProphecy */
    private $resourceServer;

    /** @var ResponseInterface&MockObject */
    private $response;

    /** @var callable */
    private $responseFactory;

    /** @var UserInterface|ObjectProphecy */
    private $user;

    /** @var Closure */
    private $userFactory;

    protected function setUp(): void
    {
        $this->container = $this->prophesize(ContainerInterface::class);
        $this->container
            ->has(ResponseFactoryInterface::class)
            ->willReturn(false);

        $this->resourceServer = $this->prophesize(ResourceServer::class);
        $this->response       = $this->createMock(ResponseInterface::class);

        $this->responseFactory = fn(): MockObject => $this->response;
        $this->user            = $this->prophesize(UserInterface::class);
        $this->userFactory     = fn(string $identity, array $roles = [], array $details = []) => $this->user->reveal();
    }

    public function testConstructor(): void
    {
        $factory = new OAuth2AdapterFactory();
        $this->assertInstanceOf(OAuth2AdapterFactory::class, $factory);
    }

    public function testInvokeWithEmptyContainer(): void
    {
        $factory = new OAuth2AdapterFactory();
        $this->container
            ->has(ResourceServer::class)
            ->willReturn(false);

        $this->expectException(Exception\InvalidConfigException::class);
        $factory($this->container->reveal());
    }

    public function testFactoryRaisesTypeErrorForNonCallableResponseFactory(): void
    {
        $this->container
            ->has(ResourceServer::class)
            ->willReturn(true);
        $this->container
            ->get(ResourceServer::class)
            ->willReturn($this->resourceServer->reveal());

        $this->container
            ->get(ResponseInterface::class)
            ->willReturn(new stdClass());

        $this->container
            ->get(UserInterface::class)
            ->willReturn($this->userFactory);

        $factory = new OAuth2AdapterFactory();

        $this->expectException(TypeError::class);
        $factory($this->container->reveal());
    }

    public function testFactoryRaisesTypeErrorWhenResponseServiceProvidesResponseInstance(): void
    {
        $this->container
            ->has(ResourceServer::class)
            ->willReturn(true);
        $this->container
            ->get(ResourceServer::class)
            ->willReturn($this->resourceServer->reveal());

        $this->container
            ->get(ResponseInterface::class)
            ->willReturn($this->response);

        $this->container
            ->get(UserInterface::class)
            ->willReturn($this->userFactory);

        $factory = new OAuth2AdapterFactory();

        $this->expectException(TypeError::class);
        $factory($this->container->reveal());
    }

    public function testFactoryReturnsInstanceWhenAppropriateDependenciesArePresentInContainer(): void
    {
        $this->container
            ->has(ResourceServer::class)
            ->willReturn(true);
        $this->container
            ->get(ResourceServer::class)
            ->willReturn($this->resourceServer->reveal());

        $this->container
            ->has(ResponseInterface::class)
            ->willReturn(true);
        $this->container
            ->get(ResponseInterface::class)
            ->willReturn($this->responseFactory);

        $this->container
            ->get(UserInterface::class)
            ->willReturn($this->userFactory);

        $factory = new OAuth2AdapterFactory();
        $adapter = $factory($this->container->reveal());

        $this->assertInstanceOf(AuthenticationInterface::class, $adapter);
    }
}
