<?php

/**
 * @see       https://github.com/mezzio/mezzio-authentication-oauth2 for the canonical source repository
 */

declare(strict_types=1);

namespace MezzioTest\Authentication\OAuth2\Grant;

use League\OAuth2\Server\Grant\RefreshTokenGrant;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use Mezzio\Authentication\OAuth2\Grant\RefreshTokenGrantFactory;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;

class RefreshTokenGrantFactoryTest extends TestCase
{
    use ProphecyTrait;

    public function testInvoke()
    {
        $mockContainer        = $this->prophesize(ContainerInterface::class);
        $mockRefreshTokenRepo = $this->prophesize(RefreshTokenRepositoryInterface::class);

        $config = [
            'authentication' => [
                'refresh_token_expire' => 'P1M',
            ],
        ];

        $mockContainer->has(RefreshTokenRepositoryInterface::class)->willReturn(true);
        $mockContainer->get(RefreshTokenRepositoryInterface::class)->willReturn($mockRefreshTokenRepo->reveal());
        $mockContainer->get('config')->willReturn($config);

        $factory = new RefreshTokenGrantFactory();

        $result = $factory($mockContainer->reveal());

        $this->assertInstanceOf(RefreshTokenGrant::class, $result);
    }
}
