<?php

declare(strict_types=1);

namespace MezzioTest\Authentication\OAuth2\Repository\Pdo;

use DateTime;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Exception\UniqueTokenIdentifierConstraintViolationException;
use Mezzio\Authentication\OAuth2\Entity\AccessTokenEntity;
use Mezzio\Authentication\OAuth2\Repository\Pdo\AccessTokenRepository;
use Mezzio\Authentication\OAuth2\Repository\Pdo\PdoService;
use PDOStatement;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

use function date;
use function time;

class AccessTokenRepositoryTest extends TestCase
{
    private AccessTokenRepository $repo;
    /** @var PdoService&MockObject */
    private PdoService $pdo;

    protected function setUp(): void
    {
        $this->pdo  = $this->createMock(PdoService::class);
        $this->repo = new AccessTokenRepository($this->pdo);
    }

    public function testPersistNewAccessTokenRaisesExceptionWhenStatementExecutionFails()
    {
        $client = $this->createMock(ClientEntityInterface::class);
        $client->expects(self::once())
            ->method('getIdentifier')
            ->willReturn('client_id');

        $scope = $this->createMock(ScopeEntityInterface::class);
        $scope->expects(self::once())
            ->method('getIdentifier')
            ->willReturn('authentication');

        $time = time();
        $date = DateTime::createFromFormat('U', (string) $time);

        $accessToken = $this->createMock(AccessTokenEntityInterface::class);
        $accessToken->method('getIdentifier')->willReturn('id');
        $accessToken->method('getUserIdentifier')->willReturn('user_id');
        $accessToken->method('getClient')->willReturn($client);
        $accessToken->method('getScopes')->willReturn([$scope]);
        $accessToken->method('getExpiryDateTime')->willReturn($date);

        $statement = $this->createMock(PDOStatement::class);
        $statement->expects(self::once())
            ->method('execute')
            ->with([
                ':id'         => 'id',
                ':user_id'    => 'user_id',
                ':client_id'  => 'client_id',
                ':scopes'     => 'authentication',
                ':revoked'    => 0,
                ':expires_at' => date('Y-m-d H:i:s', $time),
            ])
            ->willReturn(false);

        $this->pdo->expects(self::once())
            ->method('prepare')
            ->with(self::callback(function (string $arg): bool {
                self::assertStringContainsString('INSERT INTO oauth_access_tokens', $arg);

                return true;
            }))
            ->willReturn($statement);

        $this->expectException(UniqueTokenIdentifierConstraintViolationException::class);
        $this->repo->persistNewAccessToken($accessToken);
    }

    public function testIsAccessTokenRevokedReturnsFalseWhenStatementFailsExecution()
    {
        $statement = $this->createMock(PDOStatement::class);
        $statement->expects(self::once())
            ->method('bindParam')
            ->with(':tokenId', 'token_id');
        $statement->expects(self::once())
            ->method('execute')
            ->willReturn(false);
        $statement->expects(self::never())
            ->method('fetch');

        $this->pdo->expects(self::once())
            ->method('prepare')
            ->with(self::callback(function (string $sql): bool {
                self::assertStringContainsString('SELECT revoked FROM oauth_access_tokens', $sql);

                return true;
            }))->willReturn($statement);

        $this->assertFalse($this->repo->isAccessTokenRevoked('token_id'));
    }

    public function testIsAccessTokenRevokedReturnsFalseWhenRowDoesNotContainRevokedFlag()
    {
        $statement = $this->createMock(PDOStatement::class);
        $statement->expects(self::once())
            ->method('bindParam')
            ->with(':tokenId', 'token_id');
        $statement->expects(self::once())
            ->method('execute')
            ->willReturn(true);
        $statement->expects(self::once())
            ->method('fetch')
            ->willReturn([]);

        $this->pdo->expects(self::once())
            ->method('prepare')
            ->with(self::callback(function (string $sql): bool {
                self::assertStringContainsString('SELECT revoked FROM oauth_access_tokens', $sql);

                return true;
            }))->willReturn($statement);

        $this->assertFalse($this->repo->isAccessTokenRevoked('token_id'));
    }

    public function testIsAccessTokenRevokedReturnsFalseWhenRowRevokedFlagIsFalse()
    {
        $statement = $this->createMock(PDOStatement::class);
        $statement->expects(self::once())
            ->method('bindParam')
            ->with(':tokenId', 'token_id');
        $statement->expects(self::once())
            ->method('execute')
            ->willReturn(true);
        $statement->expects(self::once())
            ->method('fetch')
            ->willReturn(['revoked' => 0]);

        $this->pdo->expects(self::once())
            ->method('prepare')
            ->with(self::callback(function (string $sql): bool {
                self::assertStringContainsString('SELECT revoked FROM oauth_access_tokens', $sql);

                return true;
            }))->willReturn($statement);

        $this->assertFalse($this->repo->isAccessTokenRevoked('token_id'));
    }

    public function testIsAccessTokenRevokedReturnsTrueWhenRowRevokedFlagIsTrue()
    {
        $statement = $this->createMock(PDOStatement::class);
        $statement->expects(self::once())
            ->method('bindParam')
            ->with(':tokenId', 'token_id');
        $statement->expects(self::once())
            ->method('execute')
            ->willReturn(true);
        $statement->expects(self::once())
            ->method('fetch')
            ->willReturn(['revoked' => 1]);

        $this->pdo->expects(self::once())
            ->method('prepare')
            ->with(self::callback(function (string $sql): bool {
                self::assertStringContainsString('SELECT revoked FROM oauth_access_tokens', $sql);

                return true;
            }))->willReturn($statement);

        $this->assertTrue($this->repo->isAccessTokenRevoked('token_id'));
    }

    public function testIsAcessTokenRevokedRaisesExceptionWhenTokenIdDontExists()
    {
        $statement = $this->createMock(PDOStatement::class);
        $statement->expects(self::once())
            ->method('bindParam')
            ->with(':tokenId', 'token_id');
        $statement->expects(self::once())
            ->method('execute')
            ->willReturn(true);
        $statement->expects(self::once())
            ->method('fetch')
            ->willReturn(false);

        $this->pdo->expects(self::once())
            ->method('prepare')
            ->with(self::callback(function (string $sql): bool {
                self::assertStringContainsString('SELECT revoked FROM oauth_access_tokens', $sql);

                return true;
            }))->willReturn($statement);

        $this->expectException(OAuthServerException::class);
        $this->repo->isAccessTokenRevoked('token_id');
    }

    public function testRevokeAccessToken()
    {
        $statement = $this->createMock(PDOStatement::class);
        $statement->expects(self::once())
            ->method('bindParam')
            ->with(':tokenId', 'token_id');
        $statement->expects(self::once())
            ->method('bindValue')
            ->with(':revoked', 1);
        $statement->expects(self::once())
            ->method('execute')
            ->willReturn(true);

        $this->pdo->expects(self::once())
            ->method('prepare')
            ->with(self::callback(function (string $sql): bool {
                self::assertStringContainsString('UPDATE oauth_access_tokens SET revoked=:revoked', $sql);

                return true;
            }))->willReturn($statement);

        $this->repo->revokeAccessToken('token_id');
    }

    public function testGetNewToken()
    {
        $client      = $this->createMock(ClientEntityInterface::class);
        $accessToken = $this->repo->getNewToken($client, []);
        $this->assertInstanceOf(AccessTokenEntity::class, $accessToken);
        $this->assertEquals($client, $accessToken->getClient());
        $this->assertEquals([], $accessToken->getScopes());
    }

    public function testGetNewTokenWithScopeAndIndentifier()
    {
        $client         = $this->createMock(ClientEntityInterface::class);
        $scopes         = [$this->createMock(ScopeEntityInterface::class)];
        $userIdentifier = 'foo';

        $accessToken = $this->repo->getNewToken($client, $scopes, $userIdentifier);
        $this->assertInstanceOf(AccessTokenEntity::class, $accessToken);
        $this->assertEquals($client, $accessToken->getClient());
        $this->assertEquals($scopes, $accessToken->getScopes());
        $this->assertEquals($userIdentifier, $accessToken->getUserIdentifier());
    }
}
