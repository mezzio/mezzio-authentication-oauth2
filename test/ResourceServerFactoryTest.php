<?php

declare(strict_types=1);

namespace MezzioTest\Authentication\OAuth2;

use Generator;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use League\OAuth2\Server\ResourceServer;
use Mezzio\Authentication\OAuth2\Exception;
use Mezzio\Authentication\OAuth2\ResourceServerFactory;
use PHPUnit\Framework\TestCase;

class ResourceServerFactoryTest extends TestCase
{
    private const PUBLIC_KEY = __DIR__ . '/TestAsset/public.key';

    private const PUBLIC_KEY_EXTENDED = [
        'key_or_path'           => self::PUBLIC_KEY,
        'pass_phrase'           => 'test',
        'key_permissions_check' => false,
    ];

    private InMemoryContainer $container;

    protected function setUp(): void
    {
        $this->container = new InMemoryContainer();
    }

    public function testConstructor(): void
    {
        $factory = new ResourceServerFactory();
        self::assertInstanceOf(ResourceServerFactory::class, $factory);
    }

    public function testInvokeWithEmptyConfig(): void
    {
        $this->container->set('config', []);
        $factory = new ResourceServerFactory();

        $this->expectException(Exception\InvalidConfigException::class);
        $factory($this->container);
    }

    public function testInvokeWithConfigWithoutRepository(): void
    {
        $this->container->set('config', [
            'authentication' => [
                'public_key' => self::PUBLIC_KEY,
            ],
        ]);

        $factory = new ResourceServerFactory();

        $this->expectException(Exception\InvalidConfigException::class);
        $factory($this->container);
    }

    public function testInvokeWithConfigAndRepository(): void
    {
        $this->container->set('config', [
            'authentication' => [
                'public_key' => self::PUBLIC_KEY,
            ],
        ]);
        $this->container->set(
            AccessTokenRepositoryInterface::class,
            $this->createMock(AccessTokenRepositoryInterface::class)
        );

        $factory        = new ResourceServerFactory();
        $resourceServer = $factory($this->container);
        self::assertInstanceOf(ResourceServer::class, $resourceServer);
    }

    /** @return Generator<array-key, array{0: array}> */
    public function getExtendedKeyConfigs(): Generator
    {
        $extendedConfig = self::PUBLIC_KEY_EXTENDED;

        yield [$extendedConfig];

        unset($extendedConfig['pass_phrase']);
        yield [$extendedConfig];

        unset($extendedConfig['key_permissions_check']);
        yield [$extendedConfig];
    }

    /**
     * @dataProvider getExtendedKeyConfigs
     */
    public function testInvokeWithValidExtendedKey(array $keyConfig): void
    {
        $this->container->set('config', [
            'authentication' => [
                'public_key' => $keyConfig,
            ],
        ]);
        $this->container->set(
            AccessTokenRepositoryInterface::class,
            $this->createMock(AccessTokenRepositoryInterface::class)
        );

        $factory        = new ResourceServerFactory();
        $resourceServer = $factory($this->container);
        self::assertInstanceOf(ResourceServer::class, $resourceServer);
    }

    /** @return Generator<array-key, array{0: array}> */
    public function getInvalidExtendedKeyConfigs(): Generator
    {
        $extendedConfig = self::PUBLIC_KEY_EXTENDED;

        unset($extendedConfig['key_or_path']);
        yield [$extendedConfig];
    }

    /**
     * @dataProvider getInvalidExtendedKeyConfigs
     */
    public function testInvokeWithInvalidExtendedKey(array $keyConfig): void
    {
        $this->container->set('config', [
            'authentication' => [
                'public_key' => $keyConfig,
            ],
        ]);
        $this->container->set(
            AccessTokenRepositoryInterface::class,
            $this->createMock(AccessTokenRepositoryInterface::class)
        );

        $factory = new ResourceServerFactory();

        $this->expectException(Exception\InvalidConfigException::class);
        $factory($this->container);
    }
}
