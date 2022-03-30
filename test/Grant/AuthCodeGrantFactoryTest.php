<?php

declare(strict_types=1);

namespace MezzioTest\Authentication\OAuth2\Grant;

use League\OAuth2\Server\Grant\AuthCodeGrant;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use Mezzio\Authentication\OAuth2\Grant\AuthCodeGrantFactory;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;

class AuthCodeGrantFactoryTest extends TestCase
{
    use ProphecyTrait;

    public function testInvoke()
    {
        $mockContainer        = $this->prophesize(ContainerInterface::class);
        $mockAuthRepo         = $this->prophesize(AuthCodeRepositoryInterface::class);
        $mockRefreshTokenRepo = $this->prophesize(RefreshTokenRepositoryInterface::class);

        $config = [
            'authentication' => [
                'auth_code_expire'     => 'PT10M',
                'refresh_token_expire' => 'P1M',
            ],
        ];

        $mockContainer->has(AuthCodeRepositoryInterface::class)->willReturn(true);
        $mockContainer->has(RefreshTokenRepositoryInterface::class)->willReturn(true);
        $mockContainer->get('config')->willReturn($config);
        $mockContainer->get(AuthCodeRepositoryInterface::class)->willReturn($mockAuthRepo->reveal());
        $mockContainer->get(RefreshTokenRepositoryInterface::class)->willReturn($mockRefreshTokenRepo->reveal());

        $factory = new AuthCodeGrantFactory();

        $result = $factory($mockContainer->reveal());

        $this->assertInstanceOf(AuthCodeGrant::class, $result);
    }
}
