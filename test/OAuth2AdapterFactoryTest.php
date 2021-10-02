<?php

declare(strict_types=1);

namespace MezzioTest\Authentication\OAuth2;

use League\OAuth2\Server\ResourceServer;
use Mezzio\Authentication\AuthenticationInterface;
use Mezzio\Authentication\OAuth2\Exception;
use Mezzio\Authentication\OAuth2\OAuth2Adapter;
use Mezzio\Authentication\OAuth2\OAuth2AdapterFactory;
use Mezzio\Authentication\UserInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;
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

    /** @var ResponseInterface|ObjectProphecy */
    private $response;

    /** @var callable */
    private $responseFactory;

    protected function setUp(): void
    {
        $this->container       = $this->prophesize(ContainerInterface::class);
        $this->resourceServer  = $this->prophesize(ResourceServer::class);
        $this->response        = $this->prophesize(ResponseInterface::class);
        $this->responseFactory = function () {
            return $this->response->reveal();
        };
        $this->user            = $this->prophesize(UserInterface::class);
        $this->userFactory     = function (
            string $identity,
            array $roles = [],
            array $details = []
        ) {
            return $this->user->reveal($identity, $roles, $details);
        };
    }

    public function testConstructor()
    {
        $factory = new OAuth2AdapterFactory();
        $this->assertInstanceOf(OAuth2AdapterFactory::class, $factory);
    }

    public function testInvokeWithEmptyContainer()
    {
        $factory = new OAuth2AdapterFactory();

        $this->expectException(Exception\InvalidConfigException::class);
        $factory($this->container->reveal());
    }

    public function testFactoryRaisesTypeErrorForNonCallableResponseFactory()
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
        $adapter = $factory($this->container->reveal());
    }

    public function testFactoryRaisesTypeErrorWhenResponseServiceProvidesResponseInstance()
    {
        $this->container
            ->has(ResourceServer::class)
            ->willReturn(true);
        $this->container
            ->get(ResourceServer::class)
            ->willReturn($this->resourceServer->reveal());

        $this->container
            ->get(ResponseInterface::class)
            ->will([$this->response, 'reveal']);

        $this->container
            ->get(UserInterface::class)
            ->willReturn($this->userFactory);

        $factory = new OAuth2AdapterFactory();

        $this->expectException(TypeError::class);
        $adapter = $factory($this->container->reveal());
    }

    public function testFactoryReturnsInstanceWhenAppropriateDependenciesArePresentInContainer()
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

        $this->assertInstanceOf(OAuth2Adapter::class, $adapter);
        $this->assertInstanceOf(AuthenticationInterface::class, $adapter);
    }
}
