<?php

/**
 * @see       https://github.com/mezzio/mezzio-authentication-oauth2 for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-authentication-oauth2/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-authentication-oauth2/blob/master/LICENSE.md New BSD License
 */

namespace MezzioTest\Authentication\OAuth2\Repository\Pdo;

use Mezzio\Authentication\OAuth2\Exception;
use Mezzio\Authentication\OAuth2\Repository\Pdo\PdoService;
use Mezzio\Authentication\OAuth2\Repository\Pdo\PdoServiceFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class PdoServiceFactoryTest extends TestCase
{
    public function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);
        $this->factory = new PdoServiceFactory();
    }

    public function invalidConfiguration()
    {
        // @codingStandardsIgnoreStart
        return [
            'no-config-service'                   => [false, [], 'PDO configuration is missing'],
            'config-empty'                        => [true, [], 'PDO configuration is missing'],
            'config-authentication-empty'         => [true, ['authentication' => []], 'PDO configuration is missing'],
            'config-authentication-pdo-empty'     => [true, ['authentication' => ['pdo' => null]], 'PDO configuration is missing'],
            'config-authentication-pdo-dsn-empty' => [true, ['authentication' => ['pdo' => ['dsn' => null]]], 'DSN configuration is missing'],
        ];
        // @codingStandardsIgnoreEnd
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
}
