<?php

declare(strict_types=1);

namespace MezzioTest\Authentication\OAuth2;

use League\OAuth2\Server\AuthorizationServer;
use Mezzio\Authentication\OAuth2\TokenEndpointHandler;
use Mezzio\Authentication\OAuth2\TokenEndpointHandlerFactory;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use TypeError;

/**
 * @covers \Mezzio\Authentication\OAuth2\TokenEndpointHandlerFactory
 */
class TokenEndpointHandlerFactoryTest extends TestCase
{
    use ProphecyTrait;

    /** @var TokenEndpointHandlerFactory */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new TokenEndpointHandlerFactory();
        parent::setUp();
    }

    public function testEmptyContainerThrowsTypeError()
    {
        $container = $this->prophesize(ContainerInterface::class);

        $this->expectException(TypeError::class);
        ($this->subject)($container);
    }

    public function testCreatesTokenEndpointHandler()
    {
        $server          = $this->prophesize(AuthorizationServer::class);
        $responseFactory = function () {
        };
        $container       = $this->prophesize(ContainerInterface::class);

        $container->get(AuthorizationServer::class)
            ->willReturn($server->reveal());
        $container->get(ResponseInterface::class)
            ->willReturn($responseFactory);

        self::assertInstanceOf(TokenEndpointHandler::class, ($this->subject)($container->reveal()));
    }

    public function testDirectResponseInstanceFromContainerThrowsTypeError()
    {
        $server    = $this->prophesize(AuthorizationServer::class);
        $container = $this->prophesize(ContainerInterface::class);

        $container->get(AuthorizationServer::class)
            ->willReturn($server->reveal());
        $container->get(ResponseInterface::class)
            ->willReturn($this->prophesize(ResponseInterface::class)->reveal());

        $this->expectException(TypeError::class);
        ($this->subject)($container->reveal());
    }
}
