<?php

declare(strict_types=1);

namespace MezzioTest\Authentication\OAuth2\Repository\Pdo;

use Mezzio\Authentication\OAuth2\Repository\Pdo\AbstractRepository;
use Mezzio\Authentication\OAuth2\Repository\Pdo\PdoService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AbstractRepositoryTest extends TestCase
{
    /** @var PdoService&MockObject */
    private PdoService|MockObject $pdo;

    protected function setUp(): void
    {
        $this->pdo = $this->createMock(PdoService::class);
    }

    public function testConstructor(): void
    {
        $abstract = new AbstractRepository($this->pdo);
        $this->assertInstanceOf(AbstractRepository::class, $abstract);
    }

    public function testScopesToStringWithEmptyArray(): void
    {
        $proxy  = new class ($this->pdo) extends AbstractRepository {
            public function scopesToString(array $scopes): string
            {
                return parent::scopesToString($scopes);
            }
        };
        $result = $proxy->scopesToString([]);
        $this->assertEquals('', $result);
    }
}
