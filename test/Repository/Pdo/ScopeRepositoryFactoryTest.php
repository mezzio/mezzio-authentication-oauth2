<?php

declare(strict_types=1);

namespace MezzioTest\Authentication\OAuth2\Repository\Pdo;

use Mezzio\Authentication\OAuth2\Repository\Pdo\PdoService;
use Mezzio\Authentication\OAuth2\Repository\Pdo\ScopeRepository;
use Mezzio\Authentication\OAuth2\Repository\Pdo\ScopeRepositoryFactory;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;

class ScopeRepositoryFactoryTest extends TestCase
{
    use ProphecyTrait;

    private ObjectProphecy $container;
    private ObjectProphecy $pdo;

    protected function setUp(): void
    {
        $this->container = $this->prophesize(ContainerInterface::class);
        $this->pdo       = $this->prophesize(PdoService::class);
    }

    public function testFactory(): void
    {
        $this->container
            ->get(PdoService::class)
            ->willReturn($this->pdo->reveal());

        $factory = (new ScopeRepositoryFactory())($this->container->reveal());
        $this->assertInstanceOf(ScopeRepository::class, $factory);
    }
}
