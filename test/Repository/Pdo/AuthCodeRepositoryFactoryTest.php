<?php

declare(strict_types=1);

namespace MezzioTest\Authentication\OAuth2\Repository\Pdo;

use Mezzio\Authentication\OAuth2\Repository\Pdo\AuthCodeRepository;
use Mezzio\Authentication\OAuth2\Repository\Pdo\AuthCodeRepositoryFactory;
use Mezzio\Authentication\OAuth2\Repository\Pdo\PdoService;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class AuthCodeRepositoryFactoryTest extends TestCase
{
    public function testFactory(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::once())
            ->method('get')
            ->with(PdoService::class)
            ->willReturn($this->createMock(PdoService::class));

        $factory = (new AuthCodeRepositoryFactory())($container);
        self::assertInstanceOf(AuthCodeRepository::class, $factory);
    }
}
