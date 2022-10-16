<?php

declare(strict_types=1);

namespace MezzioTest\Authentication\OAuth2\Grant;

use League\OAuth2\Server\Grant\AuthCodeGrant;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use Mezzio\Authentication\OAuth2\Grant\AuthCodeGrantFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class AuthCodeGrantFactoryTest extends TestCase
{
    public function testInvoke(): void
    {
        $mockContainer        = $this->createMock(ContainerInterface::class);
        $mockAuthRepo         = $this->createMock(AuthCodeRepositoryInterface::class);
        $mockRefreshTokenRepo = $this->createMock(RefreshTokenRepositoryInterface::class);

        $config = [
            'authentication' => [
                'auth_code_expire'     => 'PT10M',
                'refresh_token_expire' => 'P1M',
            ],
        ];

        $mockContainer->expects(self::exactly(2))
            ->method('has')
            ->willReturnMap([
                [AuthCodeRepositoryInterface::class, true],
                [RefreshTokenRepositoryInterface::class, true],
            ]);

        $mockContainer->expects(self::atLeast(3))
            ->method('get')
            ->willReturnMap([
                ['config', $config],
                [AuthCodeRepositoryInterface::class, $mockAuthRepo],
                [RefreshTokenRepositoryInterface::class, $mockRefreshTokenRepo],
            ]);

        $factory = new AuthCodeGrantFactory();

        $result = $factory($mockContainer);

        self::assertInstanceOf(AuthCodeGrant::class, $result);
    }
}
