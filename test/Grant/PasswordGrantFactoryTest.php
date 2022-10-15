<?php

declare(strict_types=1);

namespace MezzioTest\Authentication\OAuth2\Grant;

use League\OAuth2\Server\Grant\PasswordGrant;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;
use Mezzio\Authentication\OAuth2\Grant\PasswordGrantFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class PasswordGrantFactoryTest extends TestCase
{
    public function testInvoke(): void
    {
        $mockContainer        = $this->createMock(ContainerInterface::class);
        $mockUserRepo         = $this->createMock(UserRepositoryInterface::class);
        $mockRefreshTokenRepo = $this->createMock(RefreshTokenRepositoryInterface::class);

        $config = [
            'authentication' => [
                'refresh_token_expire' => 'P1M',
            ],
        ];

        $mockContainer->expects(self::exactly(2))
            ->method('has')
            ->willReturnMap([
                [UserRepositoryInterface::class, true],
                [RefreshTokenRepositoryInterface::class, true],
            ]);
        $mockContainer->expects(self::exactly(3))
            ->method('get')
            ->willReturnMap([
                [UserRepositoryInterface::class, $mockUserRepo],
                [RefreshTokenRepositoryInterface::class, $mockRefreshTokenRepo],
                ['config', $config],
            ]);

        $factory = new PasswordGrantFactory();

        $result = $factory($mockContainer);

        self::assertInstanceOf(PasswordGrant::class, $result);
    }
}
