<?php

declare(strict_types=1);

namespace MezzioTest\Authentication\OAuth2\Grant;

use League\OAuth2\Server\Grant\ClientCredentialsGrant;
use Mezzio\Authentication\OAuth2\Grant\ClientCredentialsGrantFactory;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;

class ClientCredentialsGrantFactoryTest extends TestCase
{
    use ProphecyTrait;

    public function testInvoke(): void
    {
        $mockContainer = $this->prophesize(ContainerInterface::class);

        $factory = new ClientCredentialsGrantFactory();

        $result = $factory($mockContainer->reveal());

        $this->assertInstanceOf(ClientCredentialsGrant::class, $result);
    }
}
