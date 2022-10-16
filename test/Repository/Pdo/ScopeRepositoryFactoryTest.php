<?php

declare(strict_types=1);

namespace MezzioTest\Authentication\OAuth2\Repository\Pdo;

use Mezzio\Authentication\OAuth2\Repository\Pdo\PdoService;
use Mezzio\Authentication\OAuth2\Repository\Pdo\ScopeRepository;
use Mezzio\Authentication\OAuth2\Repository\Pdo\ScopeRepositoryFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class ScopeRepositoryFactoryTest extends TestCase
{
    public function testFactory(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::once())
            ->method('get')
            ->with(PdoService::class)
            ->willReturn($this->createMock(PdoService::class));

        $factory = (new ScopeRepositoryFactory())($container);
        self::assertInstanceOf(ScopeRepository::class, $factory);
    }
}
