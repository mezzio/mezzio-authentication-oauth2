<?php

declare(strict_types=1);

namespace MezzioTest\Authentication\OAuth2\Repository\Pdo;

use Mezzio\Authentication\OAuth2\Repository\Pdo\AccessTokenRepository;
use Mezzio\Authentication\OAuth2\Repository\Pdo\AccessTokenRepositoryFactory;
use Mezzio\Authentication\OAuth2\Repository\Pdo\PdoService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class AccessTokenRepositoryFactoryTest extends TestCase
{
    /** @var ContainerInterface&MockObject */
    private ContainerInterface $container;
    /** @var PdoService&MockObject */
    private PdoService $pdo;

    protected function setUp(): void
    {
        $this->container = $this->createMock(ContainerInterface::class);
        $this->pdo       = $this->createMock(PdoService::class);
    }

    public function testFactory(): void
    {
        $this->container->expects(self::once())
            ->method('get')
            ->with(PdoService::class)
            ->willReturn($this->pdo);

        $factory = (new AccessTokenRepositoryFactory())($this->container);
        self::assertInstanceOf(AccessTokenRepository::class, $factory);
    }
}
