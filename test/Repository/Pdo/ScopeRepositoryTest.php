<?php

declare(strict_types=1);

namespace MezzioTest\Authentication\OAuth2\Repository\Pdo;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use Mezzio\Authentication\OAuth2\Entity\ScopeEntity;
use Mezzio\Authentication\OAuth2\Repository\Pdo\PdoService;
use Mezzio\Authentication\OAuth2\Repository\Pdo\ScopeRepository;
use PDOStatement;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ScopeRepositoryTest extends TestCase
{
    /** @var PdoService&MockObject **/
    private PdoService $pdo;
    private ScopeRepository $repo;

    protected function setUp(): void
    {
        $this->pdo  = $this->createMock(PdoService::class);
        $this->repo = new ScopeRepository($this->pdo);
    }

    public function testGetScopeEntityByIdentifierReturnsNullWhenStatementExecutionFails(): void
    {
        $statement = $this->createMock(PDOStatement::class);
        $statement->expects(self::once())->method('bindParam')->with(':identifier', 'id');
        $statement->expects(self::once())->method('execute')->willReturn(false);
        $statement->expects(self::never())->method('fetch');

        $this->pdo->expects(self::once())
            ->method('prepare')
            ->with(self::stringContains('SELECT id FROM oauth_scopes'))
            ->willReturn($statement);

        self::assertNull($this->repo->getScopeEntityByIdentifier('id'));
    }

    public function testGetScopeEntityByIdentifierReturnsNullWhenReturnedRowDoesNotHaveIdentifier(): void
    {
        $statement = $this->createMock(PDOStatement::class);
        $statement->expects(self::once())->method('bindParam')->with(':identifier', 'id');
        $statement->expects(self::once())->method('execute')->willReturn(true);
        $statement->expects(self::once())->method('fetch')->willReturn([]);

        $this->pdo->expects(self::once())
            ->method('prepare')
            ->with(self::stringContains('SELECT id FROM oauth_scopes'))
            ->willReturn($statement);

        self::assertNull($this->repo->getScopeEntityByIdentifier('id'));
    }

    public function testGetScopeEntityByIndentifierReturnsScopes(): void
    {
        $statement = $this->createMock(PDOStatement::class);
        $statement->expects(self::once())->method('bindParam')->with(':identifier', 'id');
        $statement->expects(self::once())->method('execute')->willReturn(true);
        $statement->expects(self::once())->method('fetch')->willReturn([
            'id' => 'foo',
        ]);

        $this->pdo->expects(self::once())
            ->method('prepare')
            ->with(self::stringContains('SELECT id FROM oauth_scopes'))
            ->willReturn($statement);

        $scope = $this->repo->getScopeEntityByIdentifier('id');
        self::assertInstanceOf(ScopeEntity::class, $scope);
        self::assertEquals('foo', $scope->getIdentifier());
    }

    public function testFinalizeScopesWithEmptyScopes(): void
    {
        $clientEntity = $this->createMock(ClientEntityInterface::class);
        $scopes       = $this->repo->finalizeScopes([], 'foo', $clientEntity);
        self::assertEquals([], $scopes);
    }
}
