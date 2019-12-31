<?php

/**
 * @see       https://github.com/mezzio/mezzio-authentication-oauth2 for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-authentication-oauth2/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-authentication-oauth2/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace MezzioTest\Authentication\OAuth2\Repository\Pdo;

use Mezzio\Authentication\OAuth2\Repository\Pdo\PdoService;
use Mezzio\Authentication\OAuth2\Repository\Pdo\ScopeRepository;
use PDOStatement;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

class ScopeRepositoryTest extends TestCase
{
    public function setUp()
    {
        $this->pdo = $this->prophesize(PdoService::class);
        $this->repo = new ScopeRepository($this->pdo->reveal());
    }

    public function testGetScopeEntityByIdentifierReturnsNullWhenStatementExecutionFails()
    {
        $statement = $this->prophesize(PDOStatement::class);
        $statement->bindParam(':identifier', 'id')->shouldBeCalled();
        $statement->execute()->willReturn(false)->shouldBeCalled();
        $statement->fetch()->shouldNotBeCalled();

        $this->pdo
            ->prepare(Argument::containingString('SELECT id FROM oauth_scopes'))
            ->will([$statement, 'reveal']);

        $this->assertNull($this->repo->getScopeEntityByIdentifier('id'));
    }

    public function testGetScopeEntityByIdentifierReturnsNullWhenReturnedRowDoesNotHaveIdentifier()
    {
        $statement = $this->prophesize(PDOStatement::class);
        $statement->bindParam(':identifier', 'id')->shouldBeCalled();
        $statement->execute()->shouldBeCalled();
        $statement->fetch()->willReturn([])->shouldBeCalled();

        $this->pdo
            ->prepare(Argument::containingString('SELECT id FROM oauth_scopes'))
            ->will([$statement, 'reveal']);

        $this->assertNull($this->repo->getScopeEntityByIdentifier('id'));
    }
}
