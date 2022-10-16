<?php

declare(strict_types=1);

namespace MezzioTest\Authentication\OAuth2\Repository\Pdo;

use DateTime;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Exception\UniqueTokenIdentifierConstraintViolationException;
use Mezzio\Authentication\OAuth2\Entity\AuthCodeEntity;
use Mezzio\Authentication\OAuth2\Repository\Pdo\AuthCodeRepository;
use Mezzio\Authentication\OAuth2\Repository\Pdo\PdoService;
use PDOStatement;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

use function date;
use function time;

class AuthCodeRepositoryTest extends TestCase
{
    /** @var PdoService&MockObject */
    private PdoService $pdo;
    private AuthCodeRepository $repo;

    protected function setUp(): void
    {
        $this->pdo  = $this->createMock(PdoService::class);
        $this->repo = new AuthCodeRepository($this->pdo);
    }

    public function testPersistNewAuthCodeRaisesExceptionWhenStatementExecutionFails(): void
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

        $authCode = $this->createMock(AuthCodeEntity::class);
        $authCode->method('getIdentifier')->willReturn('id');
        $authCode->method('getUserIdentifier')->willReturn('user_id');
        $authCode->method('getClient')->willReturn($client);
        $authCode->method('getScopes')->willReturn([$scope]);
        $authCode->method('getExpiryDateTime')->willReturn($date);

        $statement = $this->createMock(PDOStatement::class);
        $statement->method('bindValue')
            ->withConsecutive(
                [':id', 'id'],
                [':user_id', 'user_id'],
                [':client_id', 'client_id'],
                [':scopes', 'authentication'],
                [':revoked', 0],
                [':expires_at', date('Y-m-d H:i:s', $time)],
            );

        $statement->expects(self::once())
            ->method('execute')
            ->willReturn(false);

        $this->pdo->expects(self::once())
            ->method('prepare')
            ->with(self::stringContains('INSERT INTO oauth_auth_codes'))
            ->willReturn($statement);

        $this->expectException(UniqueTokenIdentifierConstraintViolationException::class);
        $this->repo->persistNewAuthCode($authCode);
    }

    public function testIsAuthCodeRevokedReturnsFalseForStatementExecutionFailure(): void
    {
        $statement = $this->createMock(PDOStatement::class);
        $statement->expects(self::once())
            ->method('bindParam')
            ->with(':codeId', 'code_identifier');

        $statement->expects(self::once())
            ->method('execute')
            ->willReturn(false);

        $statement->expects(self::never())
            ->method('fetch');

        $this->pdo->expects(self::once())
            ->method('prepare')
            ->with(self::stringContains('SELECT revoked FROM oauth_auth_codes'))
            ->willReturn($statement);

        self::assertFalse($this->repo->isAuthCodeRevoked('code_identifier'));
    }

    public function testIsAuthCodeRevokedReturnsTrue(): void
    {
        $statement = $this->createMock(PDOStatement::class);
        $statement->expects(self::once())
            ->method('bindParam')
            ->with(':codeId', 'code_identifier');
        $statement->expects(self::once())
            ->method('execute')
            ->willReturn(true);
        $statement->expects(self::once())
            ->method('fetch')
            ->willReturn(['revoked' => true]);

        $this->pdo->expects(self::once())
            ->method('prepare')
            ->with(self::stringContains('SELECT revoked FROM oauth_auth_codes'))
            ->willReturn($statement);

        self::assertTrue($this->repo->isAuthCodeRevoked('code_identifier'));
    }

    public function testNewAuthCode(): void
    {
        $result = $this->repo->getNewAuthCode();
        self::assertInstanceOf(AuthCodeEntity::class, $result);
    }

    public function testRevokeAuthCode(): void
    {
        $statement = $this->createMock(PDOStatement::class);
        $statement->expects(self::once())
            ->method('bindParam')
            ->with(':codeId', 'code_identifier');
        $statement->expects(self::once())
            ->method('bindValue')
            ->with(':revoked', 1);
        $statement->expects(self::once())
            ->method('execute')
            ->willReturn(true);

        $this->pdo->expects(self::once())
            ->method('prepare')
            ->with(self::stringContains('UPDATE oauth_auth_codes SET revoked=:revoked WHERE id = :codeId'))
            ->willReturn($statement);

        $this->repo->revokeAuthCode('code_identifier');
    }
}
