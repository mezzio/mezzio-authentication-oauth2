<?php

declare(strict_types=1);

namespace MezzioTest\Authentication\OAuth2\Grant;

use League\OAuth2\Server\Grant\ImplicitGrant;
use Mezzio\Authentication\OAuth2\Grant\ImplicitGrantFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class ImplicitGrantFactoryTest extends TestCase
{
    public function testInvoke(): void
    {
        $mockContainer = $this->createMock(ContainerInterface::class);

        $config = [
            'authentication' => [
                'auth_code_expire' => 'PT10M',
            ],
        ];

        $mockContainer->expects(self::once())
            ->method('get')
            ->with('config')
            ->willReturn($config);

        $factory = new ImplicitGrantFactory();

        $result = $factory($mockContainer);

        self::assertInstanceOf(ImplicitGrant::class, $result);
    }
}
