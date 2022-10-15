<?php

declare(strict_types=1);

namespace MezzioTest\Authentication\OAuth2\Grant;

use League\OAuth2\Server\Grant\RefreshTokenGrant;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use Mezzio\Authentication\OAuth2\Grant\RefreshTokenGrantFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class RefreshTokenGrantFactoryTest extends TestCase
{
    public function testInvoke(): void
    {
        $mockContainer        = $this->createMock(ContainerInterface::class);
        $mockRefreshTokenRepo = $this->createMock(RefreshTokenRepositoryInterface::class);

        $config = [
            'authentication' => [
                'refresh_token_expire' => 'P1M',
            ],
        ];

        $mockContainer->expects(self::once())
            ->method('has')
            ->with(RefreshTokenRepositoryInterface::class)
            ->willReturn(true);

        $mockContainer->expects(self::atLeast(2))
            ->method('get')
            ->willReturnMap([
                [RefreshTokenRepositoryInterface::class, $mockRefreshTokenRepo],
                ['config', $config],
            ]);

        $factory = new RefreshTokenGrantFactory();
        $result  = $factory($mockContainer);

        self::assertInstanceOf(RefreshTokenGrant::class, $result);
    }
}
