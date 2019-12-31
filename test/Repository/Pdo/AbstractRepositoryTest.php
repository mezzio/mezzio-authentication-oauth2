<?php

/**
 * @see       https://github.com/mezzio/mezzio-authentication-oauth2 for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-authentication-oauth2/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-authentication-oauth2/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace MezzioTest\Authentication\OAuth2\Repository\Pdo;

use Mezzio\Authentication\OAuth2\Repository\Pdo\AbstractRepository;
use Mezzio\Authentication\OAuth2\Repository\Pdo\PdoService;
use PHPUnit\Framework\TestCase;

class AbstractRepositoryTest extends TestCase
{
    public function setUp()
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
