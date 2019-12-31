<?php

/**
 * @see       https://github.com/mezzio/mezzio-authentication-oauth2 for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-authentication-oauth2/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-authentication-oauth2/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace MezzioTest\Authentication\OAuth2\Grant;

use League\OAuth2\Server\Grant\ImplicitGrant;
use Mezzio\Authentication\OAuth2\Grant\ImplicitGrantFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class ImplicitGrantFactoryTest extends TestCase
{
    public function testInvoke()
    {
        $mockContainer = $this->prophesize(ContainerInterface::class);

        $config = [
            'authentication' => [
                'auth_code_expire' => 'PT10M',
            ]
        ];

        $mockContainer->get('config')->willReturn($config);

        $factory = new ImplicitGrantFactory();

        $result = $factory($mockContainer->reveal());

        $this->assertInstanceOf(ImplicitGrant::class, $result);
    }
}
