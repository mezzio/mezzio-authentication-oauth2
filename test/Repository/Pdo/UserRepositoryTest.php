<?php

declare(strict_types=1);

namespace MezzioTest\Authentication\OAuth2\Repository\Pdo;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use Mezzio\Authentication\OAuth2\Entity\UserEntity;
use Mezzio\Authentication\OAuth2\Repository\Pdo\PdoService;
use Mezzio\Authentication\OAuth2\Repository\Pdo\UserRepository;
use PDOStatement;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

use function password_hash;

use const PASSWORD_DEFAULT;

class UserRepositoryTest extends TestCase
{
    /** @var PdoService&MockObject **/
    private PdoService $pdo;
    private UserRepository $repo;

    protected function setUp(): void
    {
        $this->pdo  = $this->createMock(PdoService::class);
        $this->repo = new UserRepository($this->pdo);
    }

    public function testGetUserEntityByCredentialsReturnsNullIfStatementExecutionReturnsFalse(): void
    {
        $statement = $this->createMock(PDOStatement::class);
        $statement->expects(self::once())->method('bindParam')->with(':username', 'username');
        $statement->expects(self::once())->method('execute')->willReturn(false);

        $this->pdo->expects(self::once())
            ->method('prepare')
            ->with(self::stringContains('SELECT password FROM oauth_users'))
            ->willReturn($statement);

        $client = $this->createMock(ClientEntityInterface::class);

        self::assertNull(
            $this->repo->getUserEntityByUserCredentials(
                'username',
                'password',
                'auth',
                $client
            )
        );
    }

    public function testGetUserEntityByCredentialsReturnsNullIfPasswordVerificationFails(): void
    {
        $statement = $this->createMock(PDOStatement::class);
        $statement->expects(self::once())->method('bindParam')->with(':username', 'username');
        $statement->expects(self::once())->method('execute')->willReturn(true);
        $statement->expects(self::once())->method('fetch')->willReturn([
            'password' => 'not-the-same-password',
        ]);

        $this->pdo->expects(self::once())
            ->method('prepare')
            ->with(self::stringContains('SELECT password FROM oauth_users'))
            ->willReturn($statement);

        $client = $this->createMock(ClientEntityInterface::class);

        self::assertNull(
            $this->repo->getUserEntityByUserCredentials(
                'username',
                'password',
                'auth',
                $client
            )
        );
    }

    public function testGetUserEntityByCredentialsReturnsNullIfUserIsNotFound(): void
    {
        $statement = $this->createMock(PDOStatement::class);
        $statement->expects(self::once())->method('bindParam')->with(':username', 'username');
        $statement->expects(self::once())->method('execute')->willReturn(true);
        $statement->expects(self::once())->method('fetch')->willReturn(null);

        $this->pdo->expects(self::once())
            ->method('prepare')
            ->with(self::stringContains('SELECT password FROM oauth_users'))
            ->willReturn($statement);

        $client = $this->createMock(ClientEntityInterface::class);

        self::assertNull(
            $this->repo->getUserEntityByUserCredentials(
                'username',
                'password',
                'auth',
                $client
            )
        );
    }

    public function testGetUserEntityByCredentialsReturnsEntity(): void
    {
        $statement = $this->createMock(PDOStatement::class);
        $statement->expects(self::once())->method('bindParam')->with(':username', 'username');
        $statement->expects(self::once())->method('execute')->willReturn(true);
        $statement->expects(self::once())->method('fetch')->willReturn([
            'password' => password_hash('password', PASSWORD_DEFAULT),
        ]);

        $this->pdo->expects(self::once())
            ->method('prepare')
            ->with(self::stringContains('SELECT password FROM oauth_users WHERE username = :username'))
            ->willReturn($statement);

        $client = $this->createMock(ClientEntityInterface::class);

        $entity = $this->repo->getUserEntityByUserCredentials(
            'username',
            'password',
            'auth',
            $client
        );
        self::assertInstanceOf(UserEntity::class, $entity);
        self::assertEquals('username', $entity->getIdentifier());
    }
}
