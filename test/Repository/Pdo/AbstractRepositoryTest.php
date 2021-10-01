<?php

declare(strict_types=1);

namespace MezzioTest\Authentication\OAuth2\Repository\Pdo;

use Mezzio\Authentication\OAuth2\Repository\Pdo\AbstractRepository;
use Mezzio\Authentication\OAuth2\Repository\Pdo\PdoService;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class AbstractRepositoryTest extends TestCase
{
    use ProphecyTrait;

    protected function setUp() : void
    {
        $this->pdo = $this->prophesize(PdoService::class);
    }

    public function testConstructor()
    {
        $abstract = new AbstractRepository($this->pdo->reveal());
        $this->assertInstanceOf(AbstractRepository::class, $abstract);
    }

    public function testScopesToStringWithEmptyArray()
    {
        $proxy = new class($this->pdo->reveal()) extends AbstractRepository {
            public function scopesToString(array $scopes): string
            {
                return parent::scopesToString($scopes);
            }
        };
        $result = $proxy->scopesToString([]);
        $this->assertEquals('', $result);
    }
}
