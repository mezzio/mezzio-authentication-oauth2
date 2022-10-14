<?php

declare(strict_types=1);

namespace MezzioTest\Authentication\OAuth2\Repository\Pdo;

use Mezzio\Authentication\OAuth2\Repository\Pdo\AuthCodeRepository;
use Mezzio\Authentication\OAuth2\Repository\Pdo\AuthCodeRepositoryFactory;
use Mezzio\Authentication\OAuth2\Repository\Pdo\PdoService;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;

class AuthCodeRepositoryFactoryTest extends TestCase
{
    use ProphecyTrait;

    private ObjectProphecy $container;
    private ObjectProphecy $pdo;

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

        $factory = (new AuthCodeRepositoryFactory())($this->container->reveal());
        $this->assertInstanceOf(AuthCodeRepository::class, $factory);
    }
}
