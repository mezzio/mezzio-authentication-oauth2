<?php

/**
 * @see       https://github.com/mezzio/mezzio-authentication-oauth2 for the canonical source repository
 */

declare(strict_types=1);

namespace MezzioTest\Authentication\OAuth2\Repository\Pdo;

use Mezzio\Authentication\OAuth2\Repository\Pdo\AccessTokenRepository;
use Mezzio\Authentication\OAuth2\Repository\Pdo\AccessTokenRepositoryFactory;
use Mezzio\Authentication\OAuth2\Repository\Pdo\PdoService;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;

class AccessTokenRepositoryFactoryTest extends TestCase
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

        $factory = (new AccessTokenRepositoryFactory())($this->container->reveal());
        $this->assertInstanceOf(AccessTokenRepository::class, $factory);
    }
}
