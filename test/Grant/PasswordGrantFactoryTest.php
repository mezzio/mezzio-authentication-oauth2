<?php

/**
 * @see       https://github.com/mezzio/mezzio-authentication-oauth2 for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-authentication-oauth2/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-authentication-oauth2/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace MezzioTest\Authentication\OAuth2\Grant;

use League\OAuth2\Server\Grant\PasswordGrant;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;
use Mezzio\Authentication\OAuth2\Grant\PasswordGrantFactory;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;

class PasswordGrantFactoryTest extends TestCase
{
    use ProphecyTrait;

    public function testInvoke()
    {
        $mockContainer = $this->prophesize(ContainerInterface::class);
        $mockUserRepo = $this->prophesize(UserRepositoryInterface::class);
        $mockRefreshTokenRepo = $this->prophesize(RefreshTokenRepositoryInterface::class);

        $config = [
            'authentication' => [
                'refresh_token_expire' => 'P1M'
            ]
        ];

        $mockContainer->has(UserRepositoryInterface::class)->willReturn(true);
        $mockContainer->has(RefreshTokenRepositoryInterface::class)->willReturn(true);
        $mockContainer->get(UserRepositoryInterface::class)->willReturn($mockUserRepo->reveal());
        $mockContainer->get(RefreshTokenRepositoryInterface::class)->willReturn($mockRefreshTokenRepo->reveal());
        $mockContainer->get('config')->willReturn($config);

        $factory = new PasswordGrantFactory();

        $result = $factory($mockContainer->reveal());

        $this->assertInstanceOf(PasswordGrant::class, $result);
    }
}
