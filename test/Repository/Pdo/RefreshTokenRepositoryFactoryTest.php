<?php

declare(strict_types=1);

namespace MezzioTest\Authentication\OAuth2\Repository\Pdo;

use Mezzio\Authentication\OAuth2\Repository\Pdo\PdoService;
use Mezzio\Authentication\OAuth2\Repository\Pdo\RefreshTokenRepository;
use Mezzio\Authentication\OAuth2\Repository\Pdo\RefreshTokenRepositoryFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class RefreshTokenRepositoryFactoryTest extends TestCase
{
    public function testFactory(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::once())
            ->method('get')
            ->with(PdoService::class)
            ->willReturn($this->createMock(PdoService::class));

        $factory = (new RefreshTokenRepositoryFactory())($container);
        self::assertInstanceOf(RefreshTokenRepository::class, $factory);
    }
}
