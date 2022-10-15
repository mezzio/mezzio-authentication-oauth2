<?php

declare(strict_types=1);

namespace MezzioTest\Authentication\OAuth2\Repository\Pdo;

use Mezzio\Authentication\OAuth2\Exception;
use Mezzio\Authentication\OAuth2\Repository\Pdo\PdoService;
use Mezzio\Authentication\OAuth2\Repository\Pdo\PdoServiceFactory;
use MezzioTest\Authentication\OAuth2\InMemoryContainer;
use PDO;
use PHPUnit\Framework\TestCase;

class PdoServiceFactoryTest extends TestCase
{
    private InMemoryContainer $container;
    private PdoServiceFactory $factory;

    protected function setUp(): void
    {
        $this->container = new InMemoryContainer();
        $this->factory   = new PdoServiceFactory();
    }

    /** @return array<string, array{0: bool, 1: array, 2: string}> */
    public function invalidConfiguration(): array
    {
        // phpcs:disable
        return [
            'no-config-service'                   => [false, [], 'PDO configuration is missing'],
            'config-empty'                        => [true, [], 'PDO configuration is missing'],
            'config-authentication-empty'         => [true, ['authentication' => []], 'PDO configuration is missing'],
            'config-authentication-pdo-empty'     => [true, ['authentication' => ['pdo' => null]], 'PDO configuration is missing'],
            'config-authentication-pdo-dsn-empty' => [true, ['authentication' => ['pdo' => ['dsn' => null]]], 'DSN configuration is missing'],
        ];
        // phpcs:enable
    }

    /**
     * @dataProvider invalidConfiguration
     */
    public function testRaisesExceptionIfPdoConfigurationIsMissing(
        bool $hasConfig,
        array $config,
        string $expectedMessage
    ): void {
        if ($hasConfig) {
            $this->container->set('config', $config);
        }

        $this->expectException(Exception\InvalidConfigException::class);
        $this->expectExceptionMessage($expectedMessage);

        ($this->factory)($this->container);
    }

    public function testValidConfigurationResultsInReturnedPdoServiceInstance(): void
    {
        $this->container->set('config', [
            'authentication' => [
                'pdo' => [
                    'dsn' => 'sqlite::memory:',
                ],
            ],
        ]);

        $pdo = ($this->factory)($this->container);

        self::assertInstanceOf(PdoService::class, $pdo);
    }

    public function testValidServiceInConfigurationReturnsPdoService(): void
    {
        $mockPdo = $this->createMock(PDO::class);

        $this->container->set('config', [
            'authentication' => [
                'pdo' => 'My\Pdo\Service',
            ],
        ]);

        $this->container->set('My\Pdo\Service', $mockPdo);

        $pdo = ($this->factory)($this->container);

        self::assertInstanceOf(PDO::class, $pdo);
    }

    public function testRaisesExceptionIfPdoServiceIsInvalid(): void
    {
        $this->container->set('config', [
            'authentication' => [
                'pdo' => 'My\Invalid\Service',
            ],
        ]);

        $this->expectException(Exception\InvalidConfigException::class);

        ($this->factory)($this->container);
    }
}
