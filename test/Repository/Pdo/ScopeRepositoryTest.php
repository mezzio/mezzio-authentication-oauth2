<?php

/**
 * @see       https://github.com/mezzio/mezzio-authentication-oauth2 for the canonical source repository
 */

declare(strict_types=1);

namespace MezzioTest\Authentication\OAuth2\Repository\Pdo;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use Mezzio\Authentication\OAuth2\Entity\ScopeEntity;
use Mezzio\Authentication\OAuth2\Repository\Pdo\PdoService;
use Mezzio\Authentication\OAuth2\Repository\Pdo\ScopeRepository;
use PDOStatement;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;

class ScopeRepositoryTest extends TestCase
{
    use ProphecyTrait;

    protected function setUp(): void
    {
        $this->pdo  = $this->prophesize(PdoService::class);
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

    public function testGetScopeEntityByIndentifierReturnsScopes()
    {
        $statement = $this->prophesize(PDOStatement::class);
        $statement->bindParam(':identifier', 'id')->shouldBeCalled();
        $statement->execute()->shouldBeCalled();
        $statement->fetch()->willReturn([
            'id' => 'foo',
        ])->shouldBeCalled();

        $this->pdo
            ->prepare(Argument::containingString('SELECT id FROM oauth_scopes'))
            ->will([$statement, 'reveal']);

        $scope = $this->repo->getScopeEntityByIdentifier('id');
        $this->assertInstanceOf(ScopeEntity::class, $scope);
        $this->assertEquals('foo', $scope->getIdentifier());
    }

    public function testFinalizeScopesWithEmptyScopes()
    {
        $clientEntity = $this->prophesize(ClientEntityInterface::class);
        $scopes       = $this->repo->finalizeScopes([], 'foo', $clientEntity->reveal());
        $this->assertEquals([], $scopes);
    }
}
