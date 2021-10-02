<?php

declare(strict_types=1);

namespace MezzioTest\Authentication\OAuth2;

use Mezzio\Authentication\OAuth2\ConfigTrait;
use Mezzio\Authentication\OAuth2\Exception;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;

class ConfigTraitTest extends TestCase
{
    use ProphecyTrait;

    protected function setUp(): void
    {
        $this->trait     = $trait = new class {
            use ConfigTrait;

            /**
             * @return array|string
             */
            public function proxy(string $name, ContainerInterface $container)
            {
                return $this->$name($container);
            }
        };
        $this->config    = [
            'authentication' => [
                'private_key'          => 'xxx',
                'encryption_key'       => 'xxx',
                'access_token_expire'  => '3600',
                'refresh_token_expire' => '3600',
                'auth_code_expire'     => '120',
                'grants'               => ['xxx'],
            ],
        ];
        $this->container = $this->prophesize(ContainerInterface::class);
        $this->container
            ->get('config')
            ->willReturn($this->config);
    }

    public function testGetPrivateKeyWhenNoConfigPresentWillResultInAnException()
    {
        $this->container
            ->get('config')
            ->willReturn([]);

        $this->expectException(Exception\InvalidConfigException::class);
        $this->trait->proxy('getPrivateKey', $this->container->reveal());
    }

    public function testGetPrivateKey()
    {
        $result = $this->trait->proxy('getPrivateKey', $this->container->reveal());
        $this->assertEquals($this->config['authentication']['private_key'], $result);
    }

    public function testGetEncryptionKeyNoConfig()
    {
        $this->container
            ->get('config')
            ->willReturn([]);

        $this->expectException(Exception\InvalidConfigException::class);
        $this->trait->proxy('getEncryptionKey', $this->container->reveal());
    }

    public function testGetEncryptionKey()
    {
        $result = $this->trait->proxy('getEncryptionKey', $this->container->reveal());
        $this->assertEquals($this->config['authentication']['encryption_key'], $result);
    }

    public function testGetAccessTokenExpireNoConfig()
    {
        $this->container
            ->get('config')
            ->willReturn([]);

        $this->expectException(Exception\InvalidConfigException::class);
        $this->trait->proxy('getAccessTokenExpire', $this->container->reveal());
    }

    public function testGetAccessTokenExpire()
    {
        $result = $this->trait->proxy('getAccessTokenExpire', $this->container->reveal());
        $this->assertEquals($this->config['authentication']['access_token_expire'], $result);
    }

    public function testGetRefreshTokenExpireNoConfig()
    {
        $this->container
            ->get('config')
            ->willReturn([]);

        $this->expectException(Exception\InvalidConfigException::class);
        $this->trait->proxy('getRefreshTokenExpire', $this->container->reveal());
    }

    public function testGetRefreshTokenExpire()
    {
        $result = $this->trait->proxy('getRefreshTokenExpire', $this->container->reveal());
        $this->assertEquals($this->config['authentication']['refresh_token_expire'], $result);
    }

    public function testGetAuthCodeExpireNoConfig()
    {
        $this->container
            ->get('config')
            ->willReturn([]);

        $this->expectException(Exception\InvalidConfigException::class);
        $this->trait->proxy('getAuthCodeExpire', $this->container->reveal());
    }

    public function testGetAuthCodeExpire()
    {
        $result = $this->trait->proxy('getAuthCodeExpire', $this->container->reveal());
        $this->assertEquals($this->config['authentication']['auth_code_expire'], $result);
    }

    public function testGetGrantsConfigNoConfig()
    {
        $this->container
            ->get('config')
            ->willReturn([]);

        $this->expectException(Exception\InvalidConfigException::class);
        $this->trait->proxy('getGrantsConfig', $this->container->reveal());
    }

    public function testGetGrantsConfigNoArrayValue()
    {
        $this->container
            ->get('config')
            ->willReturn([
                'authentication' => [
                    'grants' => 'xxx',
                ],
            ]);

        $this->expectException(Exception\InvalidConfigException::class);
        $this->trait->proxy('getGrantsConfig', $this->container->reveal());
    }

    public function testGetGrantsConfig()
    {
        $result = $this->trait->proxy('getGrantsConfig', $this->container->reveal());
        $this->assertEquals($this->config['authentication']['grants'], $result);
    }

    public function testGetListenersConfigNoConfig()
    {
        $this->container
            ->get('config')
            ->willReturn([]);
        $result = $this->trait
            ->proxy('getListenersConfig', $this->container->reveal());
        $this->assertIsArray($result);
    }

    public function testGetListenersConfigNoArrayValue()
    {
        $this->expectException(Exception\InvalidConfigException::class);

        $this->container
            ->get('config')
            ->willReturn([
                'authentication' => [
                    'event_listeners' => 'xxx',
                ],
            ]);

        $this->trait->proxy('getListenersConfig', $this->container->reveal());
    }

    public function testGetListenersConfig()
    {
        $this->container->get('config')
            ->willReturn([
                'authentication' => [
                    'event_listeners' => $expected = [['xxx']],
                ],
            ]);
        $result                                    = $this->trait
            ->proxy('getListenersConfig', $this->container->reveal());
        $this->assertEquals($expected, $result);
    }

    public function testGetListenerProvidersConfigNoConfig()
    {
        $this->container
            ->get('config')
            ->willReturn([]);
        $result = $this->trait
            ->proxy('getListenerProvidersConfig', $this->container->reveal());
        $this->assertIsArray($result);
    }

    public function testGetListenerProvidersConfigNoArrayValue()
    {
        $this->expectException(Exception\InvalidConfigException::class);

        $this->container
            ->get('config')
            ->willReturn([
                'authentication' => [
                    'event_listener_providers' => 'xxx',
                ],
            ]);

        $this->trait->proxy('getListenerProvidersConfig', $this->container->reveal());
    }

    public function testGetListenerProvidersConfig()
    {
        $this->container->get('config')
            ->willReturn([
                'authentication' => [
                    'event_listener_providers' => $expected = ['xxx'],
                ],
            ]);
        $result                                             = $this->trait
            ->proxy('getListenerProvidersConfig', $this->container->reveal());
        $this->assertEquals($expected, $result);
    }
}
