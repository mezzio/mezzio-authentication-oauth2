<?php

declare(strict_types=1);

namespace MezzioTest\Authentication\OAuth2\Repository\Pdo;

use Mezzio\Authentication\OAuth2\Exception;
use Mezzio\Authentication\OAuth2\Repository\Pdo\PdoService;
use Mezzio\Authentication\OAuth2\Repository\Pdo\PdoServiceFactory;
use PDO;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;

class PdoServiceFactoryTest extends TestCase
{
    use ProphecyTrait;

    protected function setUp(): void
    {
        $this->container = $this->prophesize(ContainerInterface::class);
        $this->factory   = new PdoServiceFactory();
    }

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
    ) {
        $this->container->has('config')->willReturn($hasConfig);
        if ($hasConfig) {
            $this->container->get('config')->willReturn($config)->shouldBeCalled();
        } else {
            $this->container->get('config')->shouldNotBeCalled();
        }

        $this->expectException(Exception\InvalidConfigException::class);
        $this->expectExceptionMessage($expectedMessage);

        ($this->factory)($this->container->reveal());
    }

    public function testValidConfigurationResultsInReturnedPdoServiceInstance()
    {
        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn([
            'authentication' => [
                'pdo' => [
                    'dsn' => 'sqlite::memory:',
                ],
            ],
        ]);

        $pdo = ($this->factory)($this->container->reveal());

        $this->assertInstanceOf(PdoService::class, $pdo);
    }

    public function testValidServiceInConfigurationReturnsPdoService()
    {
        $mockPdo = $this->prophesize(PDO::class);

        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn([
            'authentication' => [
                'pdo' => 'My\Pdo\Service',
            ],
        ]);

        $this->container->has('My\Pdo\Service')->willReturn(true);
        $this->container->get('My\Pdo\Service')->willReturn($mockPdo->reveal());

        $pdo = ($this->factory)($this->container->reveal());

        $this->assertInstanceOf(PDO::class, $pdo);
    }

    public function testRaisesExceptionIfPdoServiceIsInvalid()
    {
        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn([
            'authentication' => [
                'pdo' => 'My\Invalid\Service',
            ],
        ]);

        $this->container->has('My\Invalid\Service')->willReturn(false);

        $this->expectException(Exception\InvalidConfigException::class);

        ($this->factory)($this->container->reveal());
    }
}
