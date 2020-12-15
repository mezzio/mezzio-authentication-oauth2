<?php

/**
 * @see       https://github.com/mezzio/mezzio-authentication-oauth2 for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-authentication-oauth2/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-authentication-oauth2/blob/master/LICENSE.md New BSD License
 */

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

    public function testInvoke()
    {
        $mockContainer = $this->prophesize(ContainerInterface::class);

        $factory = new ClientCredentialsGrantFactory();

        $result = $factory($mockContainer->reveal());

        $this->assertInstanceOf(ClientCredentialsGrant::class, $result);
    }
}
