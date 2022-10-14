<?php

declare(strict_types=1);

namespace MezzioTest\Authentication\OAuth2\Grant;

use League\OAuth2\Server\Grant\ImplicitGrant;
use Mezzio\Authentication\OAuth2\Grant\ImplicitGrantFactory;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;

class ImplicitGrantFactoryTest extends TestCase
{
    use ProphecyTrait;

    public function testInvoke(): void
    {
        $mockContainer = $this->prophesize(ContainerInterface::class);

        $config = [
            'authentication' => [
                'auth_code_expire' => 'PT10M',
            ],
        ];

        $mockContainer->get('config')->willReturn($config);

        $factory = new ImplicitGrantFactory();

        $result = $factory($mockContainer->reveal());

        $this->assertInstanceOf(ImplicitGrant::class, $result);
    }
}
