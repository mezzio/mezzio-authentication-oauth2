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
use Psr\Http\Message\ResponseInterface;
use stdClass;
use TypeError;

final class OAuth2AdapterFactoryTest extends TestCase
{
    private InMemoryContainer $container;

    private MockObject&ResourceServer $resourceServer;

    private MockObject&ResponseInterface $response;

    /** @var callable(): ResponseInterface */
    private $responseFactory;

    private MockObject&UserInterface $user;

    /** @var Closure(): UserInterface */
    private Closure $userFactory;

    protected function setUp(): void
    {
        $this->container      = new InMemoryContainer();
        $this->resourceServer = $this->createMock(ResourceServer::class);
        $this->response       = $this->createMock(ResponseInterface::class);

        $this->responseFactory = fn(): MockObject => $this->response;
        $this->user            = $this->createMock(UserInterface::class);
        $this->userFactory     = fn(): UserInterface => $this->user;
    }

    public function testInvokeWithEmptyContainer(): void
    {
        $factory = new OAuth2AdapterFactory();
        $this->expectException(Exception\InvalidConfigException::class);
        $factory($this->container);
    }

    public function testFactoryRaisesTypeErrorForNonCallableResponseFactory(): void
    {
        $this->container->set(ResourceServer::class, $this->resourceServer);
        $this->container->set(ResponseInterface::class, new stdClass());
        $this->container->set(UserInterface::class, $this->userFactory);

        $factory = new OAuth2AdapterFactory();
        $this->expectException(TypeError::class);
        $factory($this->container);
    }

    public function testFactoryRaisesTypeErrorWhenResponseServiceProvidesResponseInstance(): void
    {
        $this->container->set(ResourceServer::class, $this->resourceServer);
        $this->container->set(ResponseInterface::class, $this->response);
        $this->container->set(UserInterface::class, $this->userFactory);

        $factory = new OAuth2AdapterFactory();

        $this->expectException(TypeError::class);
        $factory($this->container);
    }

    public function testFactoryReturnsInstanceWhenAppropriateDependenciesArePresentInContainer(): void
    {
        $this->container->set(ResourceServer::class, $this->resourceServer);
        $this->container->set(ResponseInterface::class, $this->responseFactory);
        $this->container->set(UserInterface::class, $this->userFactory);

        $factory = new OAuth2AdapterFactory();
        $adapter = $factory($this->container);

        self::assertInstanceOf(AuthenticationInterface::class, $adapter);
    }
}
