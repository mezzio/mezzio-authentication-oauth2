<?php

/**
 * @see       https://github.com/mezzio/mezzio-authentication-oauth2 for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-authentication-oauth2/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-authentication-oauth2/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace MezzioTest\Authentication\OAuth2;

use Mezzio\Authentication\OAuth2\ConfigTrait;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class ConfigTraitTest extends TestCase
{
    public function setUp()
    {
        $this->trait = $trait = new class {
            use ConfigTrait;

            public function proxy(string $name, ContainerInterface $container)
            {
                return $this->$name($container);
            }
        };
        $this->config = [
            'authentication' => [
                'private_key' => 'xxx',
                'encryption_key' => 'xxx',
                'access_token_expire' => '3600',
                'refresh_token_expire' => '3600',
                'auth_code_expire' => '120',
                'grants' => ['xxx']
            ]
        ];
        $this->container = $this->prophesize(ContainerInterface::class);
        $this->container
            ->get('config')
            ->willReturn($this->config);
    }

    /**
     * @expectedException Mezzio\Authentication\OAuth2\Exception\InvalidConfigException
     */
    public function testGetPrivateKeyWhenNoConfigPresentWillResultInAnException()
    {
        $this->container
            ->get('config')
            ->willReturn([]);
        $this->trait->proxy('getPrivateKey', $this->container->reveal());
    }

    public function testGetPrivateKey()
    {
        $result = $this->trait->proxy('getPrivateKey', $this->container->reveal());
        $this->assertEquals($this->config['authentication']['private_key'], $result);
    }

    /**
     * @expectedException Mezzio\Authentication\OAuth2\Exception\InvalidConfigException
     */
    public function testGetEncryptionKeyNoConfig()
    {
        $this->container
            ->get('config')
            ->willReturn([]);
        $this->trait->proxy('getEncryptionKey', $this->container->reveal());
    }

    public function testGetEncryptionKey()
    {
        $result = $this->trait->proxy('getEncryptionKey', $this->container->reveal());
        $this->assertEquals($this->config['authentication']['encryption_key'], $result);
    }

    /**
     * @expectedException Mezzio\Authentication\OAuth2\Exception\InvalidConfigException
     */
    public function testGetAccessTokenExpireNoConfig()
    {
        $this->container
            ->get('config')
            ->willReturn([]);
        $this->trait->proxy('getAccessTokenExpire', $this->container->reveal());
    }

    public function testGetAccessTokenExpire()
    {
        $result = $this->trait->proxy('getAccessTokenExpire', $this->container->reveal());
        $this->assertEquals($this->config['authentication']['access_token_expire'], $result);
    }

    /**
     * @expectedException Mezzio\Authentication\OAuth2\Exception\InvalidConfigException
     */
    public function testGetRefreshTokenExpireNoConfig()
    {
        $this->container
            ->get('config')
            ->willReturn([]);
        $this->trait->proxy('getRefreshTokenExpire', $this->container->reveal());
    }

    public function testGetRefreshTokenExpire()
    {
        $result = $this->trait->proxy('getRefreshTokenExpire', $this->container->reveal());
        $this->assertEquals($this->config['authentication']['refresh_token_expire'], $result);
    }

    /**
     * @expectedException Mezzio\Authentication\OAuth2\Exception\InvalidConfigException
     */
    public function testGetAuthCodeExpireNoConfig()
    {
        $this->container
            ->get('config')
            ->willReturn([]);
        $this->trait->proxy('getAuthCodeExpire', $this->container->reveal());
    }

    public function testGetAuthCodeExpire()
    {
        $result = $this->trait->proxy('getAuthCodeExpire', $this->container->reveal());
        $this->assertEquals($this->config['authentication']['auth_code_expire'], $result);
    }

    /**
     * @expectedException Mezzio\Authentication\OAuth2\Exception\InvalidConfigException
     */
    public function testGetGrantsConfigNoConfig()
    {
        $this->container
            ->get('config')
            ->willReturn([]);
        $this->trait->proxy('getGrantsConfig', $this->container->reveal());
    }

    /**
     * @expectedException Mezzio\Authentication\OAuth2\Exception\InvalidConfigException
     */
    public function testGetGrantsConfigNoArrayValue()
    {
        $this->container
            ->get('config')
            ->willReturn([
                'authentication' => [
                    'grants' => 'xxx',
                ],
            ]);

        $this->trait->proxy('getGrantsConfig', $this->container->reveal());
    }

    public function testGetGrantsConfig()
    {
        $result = $this->trait->proxy('getGrantsConfig', $this->container->reveal());
        $this->assertEquals($this->config['authentication']['grants'], $result);
    }
}
