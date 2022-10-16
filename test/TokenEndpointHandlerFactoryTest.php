<?php

declare(strict_types=1);

namespace MezzioTest\Authentication\OAuth2;

use League\OAuth2\Server\AuthorizationServer;
use Mezzio\Authentication\OAuth2\TokenEndpointHandler;
use Mezzio\Authentication\OAuth2\TokenEndpointHandlerFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use TypeError;

/**
 * @covers \Mezzio\Authentication\OAuth2\TokenEndpointHandlerFactory
 */
class TokenEndpointHandlerFactoryTest extends TestCase
{
    private TokenEndpointHandlerFactory $subject;

    protected function setUp(): void
    {
        $this->subject = new TokenEndpointHandlerFactory();
        parent::setUp();
    }

    public function testEmptyContainerThrowsServiceNotFound(): void
    {
        $this->expectException(NotFoundExceptionInterface::class);
        ($this->subject)(new InMemoryContainer());
    }

    public function testCreatesTokenEndpointHandler(): void
    {
        $container = new InMemoryContainer();
        $container->set(AuthorizationServer::class, $this->createMock(AuthorizationServer::class));
        $container->set(ResponseInterface::class, static fn () => null);

        self::assertInstanceOf(TokenEndpointHandler::class, ($this->subject)($container));
    }

    public function testDirectResponseInstanceFromContainerThrowsTypeError(): void
    {
        $container = new InMemoryContainer();
        $container->set(AuthorizationServer::class, $this->createMock(AuthorizationServer::class));
        $container->set(ResponseInterface::class, $this->createMock(ResponseInterface::class));

        $this->expectException(TypeError::class);
        ($this->subject)($container);
    }
}
