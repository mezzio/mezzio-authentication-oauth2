<?php

declare(strict_types=1);

namespace MezzioTest\Authentication\OAuth2;

use Mezzio\Authentication\OAuth2\ConfigTrait;
use Mezzio\Authentication\OAuth2\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class ConfigTraitTest extends TestCase
{
    private object $trait;
    /** @var array{authentication: array<string, mixed>} */
    private array $config;
    /** @var ContainerInterface&MockObject */
    private ContainerInterface $container;

    protected function setUp(): void
    {
        $this->trait     = new class {
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
        $this->container = $this->createMock(ContainerInterface::class);
    }

    private function containerHasConfig(array $config): void
    {
        $this->container->expects(self::once())
            ->method('get')
            ->with('config')
            ->willReturn($config);
    }

    public function testGetPrivateKeyWhenNoConfigPresentWillResultInAnException(): void
    {
        $this->containerHasConfig([]);

        $this->expectException(Exception\InvalidConfigException::class);
        $this->trait->proxy('getPrivateKey', $this->container);
    }

    public function testGetPrivateKey(): void
    {
        $this->containerHasConfig($this->config);
        $result = $this->trait->proxy('getPrivateKey', $this->container);
        self::assertEquals($this->config['authentication']['private_key'], $result);
    }

    public function testGetPrivateKeyArray(): void
    {
        $config = [
            'authentication' => [
                'private_key' => [
                    'key_or_path'           => 'xxx',
                    'pass_phrase'           => 'test',
                    'key_permissions_check' => false,
                ],
            ],
        ];

        $this->containerHasConfig($config);

        $result = $this->trait->proxy('getPrivateKey', $this->container);
        self::assertEquals($config['authentication']['private_key'], $result);
    }

    public function testGetEncryptionKeyNoConfig(): void
    {
        $this->containerHasConfig([]);

        $this->expectException(Exception\InvalidConfigException::class);
        $this->trait->proxy('getEncryptionKey', $this->container);
    }

    public function testGetEncryptionKey(): void
    {
        $this->containerHasConfig($this->config);
        $result = $this->trait->proxy('getEncryptionKey', $this->container);
        self::assertEquals($this->config['authentication']['encryption_key'], $result);
    }

    public function testGetAccessTokenExpireNoConfig(): void
    {
        $this->containerHasConfig([]);

        $this->expectException(Exception\InvalidConfigException::class);
        $this->trait->proxy('getAccessTokenExpire', $this->container);
    }

    public function testGetAccessTokenExpire(): void
    {
        $this->containerHasConfig($this->config);

        $result = $this->trait->proxy('getAccessTokenExpire', $this->container);
        self::assertEquals($this->config['authentication']['access_token_expire'], $result);
    }

    public function testGetRefreshTokenExpireNoConfig(): void
    {
        $this->containerHasConfig([]);

        $this->expectException(Exception\InvalidConfigException::class);
        $this->trait->proxy('getRefreshTokenExpire', $this->container);
    }

    public function testGetRefreshTokenExpire(): void
    {
        $this->containerHasConfig($this->config);

        $result = $this->trait->proxy('getRefreshTokenExpire', $this->container);
        self::assertEquals($this->config['authentication']['refresh_token_expire'], $result);
    }

    public function testGetAuthCodeExpireNoConfig(): void
    {
        $this->containerHasConfig([]);

        $this->expectException(Exception\InvalidConfigException::class);
        $this->trait->proxy('getAuthCodeExpire', $this->container);
    }

    public function testGetAuthCodeExpire(): void
    {
        $this->containerHasConfig($this->config);

        $result = $this->trait->proxy('getAuthCodeExpire', $this->container);
        self::assertEquals($this->config['authentication']['auth_code_expire'], $result);
    }

    public function testGetGrantsConfigNoConfig(): void
    {
        $this->containerHasConfig([]);

        $this->expectException(Exception\InvalidConfigException::class);
        $this->trait->proxy('getGrantsConfig', $this->container);
    }

    public function testGetGrantsConfigNoArrayValue(): void
    {
        $this->containerHasConfig([
            'authentication' => [
                'grants' => 'xxx',
            ],
        ]);

        $this->expectException(Exception\InvalidConfigException::class);
        $this->trait->proxy('getGrantsConfig', $this->container);
    }

    public function testGetGrantsConfig(): void
    {
        $this->containerHasConfig($this->config);

        $result = $this->trait->proxy('getGrantsConfig', $this->container);
        self::assertEquals($this->config['authentication']['grants'], $result);
    }

    public function testGetListenersConfigNoConfig(): void
    {
        $this->containerHasConfig([]);

        $result = $this->trait
            ->proxy('getListenersConfig', $this->container);
        self::assertIsArray($result);
    }

    public function testGetListenersConfigNoArrayValue(): void
    {
        $this->expectException(Exception\InvalidConfigException::class);

        $this->containerHasConfig([
            'authentication' => [
                'event_listeners' => 'xxx',
            ],
        ]);

        $this->trait->proxy('getListenersConfig', $this->container);
    }

    public function testGetListenersConfig(): void
    {
        $this->containerHasConfig([
            'authentication' => [
                'event_listeners' => $expected = [['xxx']],
            ],
        ]);
        $result = $this->trait->proxy('getListenersConfig', $this->container);
        self::assertEquals($expected, $result);
    }

    public function testGetListenerProvidersConfigNoConfig(): void
    {
        $this->containerHasConfig([]);

        $result = $this->trait->proxy('getListenerProvidersConfig', $this->container);
        self::assertIsArray($result);
    }

    public function testGetListenerProvidersConfigNoArrayValue(): void
    {
        $this->expectException(Exception\InvalidConfigException::class);

        $this->containerHasConfig([
            'authentication' => [
                'event_listener_providers' => 'xxx',
            ],
        ]);

        $this->trait->proxy('getListenerProvidersConfig', $this->container);
    }

    public function testGetListenerProvidersConfig(): void
    {
        $this->containerHasConfig([
            'authentication' => [
                'event_listener_providers' => $expected = ['xxx'],
            ],
        ]);
        $result = $this->trait->proxy('getListenerProvidersConfig', $this->container);
        self::assertEquals($expected, $result);
    }
}
