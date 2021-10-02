<?php

declare(strict_types=1);

namespace MezzioTest\Authentication\OAuth2\Repository\Pdo;

use Mezzio\Authentication\OAuth2\Repository\Pdo\PdoService;
use Mezzio\Authentication\OAuth2\Repository\Pdo\RefreshTokenRepository;
use Mezzio\Authentication\OAuth2\Repository\Pdo\RefreshTokenRepositoryFactory;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;

class RefreshTokenRepositoryFactoryTest extends TestCase
{
    use ProphecyTrait;

    /** @var ContainerInterface */
    private $container;

    protected function setUp(): void
    {
        $this->container = $this->prophesize(ContainerInterface::class);
        $this->pdo       = $this->prophesize(PdoService::class);
    }

    public function testFactory()
    {
        $this->container
            ->get(PdoService::class)
            ->willReturn($this->pdo->reveal());

        $factory = (new RefreshTokenRepositoryFactory())($this->container->reveal());
        $this->assertInstanceOf(RefreshTokenRepository::class, $factory);
    }
}
