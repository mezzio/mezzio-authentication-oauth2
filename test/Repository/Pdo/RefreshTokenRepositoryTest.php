<?php

declare(strict_types=1);

namespace MezzioTest\Authentication\OAuth2\Repository\Pdo;

use DateTime;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Exception\UniqueTokenIdentifierConstraintViolationException;
use Mezzio\Authentication\OAuth2\Entity\RefreshTokenEntity;
use Mezzio\Authentication\OAuth2\Repository\Pdo\PdoService;
use Mezzio\Authentication\OAuth2\Repository\Pdo\RefreshTokenRepository;
use PDOStatement;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

use function date;
use function time;

class RefreshTokenRepositoryTest extends TestCase
{
    /** @var PdoService&MockObject */
    private PdoService $pdo;
    private RefreshTokenRepository $repo;

    protected function setUp(): void
    {
        $this->pdo  = $this->createMock(PdoService::class);
        $this->repo = new RefreshTokenRepository($this->pdo);
    }

    public function testPersistNewRefreshTokenRaisesExceptionWhenStatementExecutionFails(): void
    {
        $accessToken = $this->createMock(AccessTokenEntityInterface::class);
        $accessToken->expects(self::once())
            ->method('getIdentifier')
            ->willReturn('access_token_id');

        $time = time();
        $date = DateTime::createFromFormat('U', (string) $time);

        $refreshToken = $this->createMock(RefreshTokenEntityInterface::class);
        $refreshToken->expects(self::once())
            ->method('getIdentifier')
            ->willReturn('id');
        $refreshToken->expects(self::once())
            ->method('getAccessToken')
            ->willReturn($accessToken);
        $refreshToken->expects(self::once())
            ->method('getExpiryDateTime')
            ->willReturn($date);

        $statement = $this->createMock(PDOStatement::class);
        $statement->expects(self::exactly(4))
            ->method('bindValue')
            ->withConsecutive(
                [':id', 'id'],
                [':access_token_id', 'access_token_id'],
                [':revoked', 0],
                [':expires_at', date('Y-m-d H:i:s', $time)],
            );

        $statement->expects(self::once())->method('execute')->willReturn(false);

        $this->pdo->expects(self::once())
            ->method('prepare')
            ->with(self::stringContains('INSERT INTO oauth_refresh_tokens'))
            ->willReturn($statement);

        $this->expectException(UniqueTokenIdentifierConstraintViolationException::class);
        $this->repo->persistNewRefreshToken($refreshToken);
    }

    public function testIsRefreshTokenRevokedReturnsFalseWhenStatementFailsExecution(): void
    {
        $statement = $this->createMock(PDOStatement::class);
        $statement->expects(self::once())->method('bindParam')->with(':tokenId', 'token_id');
        $statement->expects(self::once())->method('execute')->willReturn(false);
        $statement->expects(self::never())->method('fetch');

        $this->pdo->expects(self::once())
            ->method('prepare')
            ->with(self::stringContains('SELECT revoked FROM oauth_refresh_tokens'))
            ->willReturn($statement);

        self::assertFalse($this->repo->isRefreshTokenRevoked('token_id'));
    }

    public function testIsRefreshTokenRevokedReturnsTrue(): void
    {
        $statement = $this->createMock(PDOStatement::class);
        $statement->expects(self::once())->method('bindParam')->with(':tokenId', 'token_id');
        $statement->expects(self::once())->method('execute')->willReturn(true);
        $statement->expects(self::once())->method('fetch')->willReturn(['revoked' => true]);

        $this->pdo->expects(self::once())
            ->method('prepare')
            ->with(self::stringContains('SELECT revoked FROM oauth_refresh_tokens'))
            ->willReturn($statement);

        self::assertTrue($this->repo->isRefreshTokenRevoked('token_id'));
    }

    public function testGetNewRefreshToken(): void
    {
        $result = $this->repo->getNewRefreshToken();
        self::assertInstanceOf(RefreshTokenEntity::class, $result);
    }

    public function testRevokeRefreshToken(): void
    {
        $statement = $this->createMock(PDOStatement::class);
        $statement->expects(self::once())->method('bindParam')->with(':tokenId', 'token_id');
        $statement->bindValue(':revoked', 1);
        $statement->expects(self::once())->method('execute')->willReturn(true);
        $statement->expects(self::never())->method('fetch');

        $this->pdo->expects(self::once())
            ->method('prepare')
            ->with(self::stringContains('UPDATE oauth_refresh_tokens SET revoked=:revoked WHERE id = :tokenId'))
            ->willReturn($statement);

        $this->repo->revokeRefreshToken('token_id');
    }
}
