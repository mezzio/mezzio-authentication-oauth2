<?php

declare(strict_types=1);

namespace MezzioTest\Authentication\OAuth2\Repository\Pdo;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use Mezzio\Authentication\OAuth2\Entity\UserEntity;
use Mezzio\Authentication\OAuth2\Repository\Pdo\PdoService;
use Mezzio\Authentication\OAuth2\Repository\Pdo\UserRepository;
use PDOStatement;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;

use function password_hash;

use const PASSWORD_DEFAULT;

class UserRepositoryTest extends TestCase
{
    use ProphecyTrait;

    protected function setUp(): void
    {
        $this->pdo  = $this->prophesize(PdoService::class);
        $this->repo = new UserRepository($this->pdo->reveal());
    }

    public function testGetUserEntityByCredentialsReturnsNullIfStatementExecutionReturnsFalse()
    {
        $statement = $this->prophesize(PDOStatement::class);
        $statement->bindParam(':username', 'username')->shouldBeCalled();
        $statement->execute()->willReturn(false);

        $this->pdo
            ->prepare(Argument::containingString('SELECT password FROM oauth_users'))
            ->will([$statement, 'reveal']);

        $client = $this->prophesize(ClientEntityInterface::class);

        $this->assertNull(
            $this->repo->getUserEntityByUserCredentials(
                'username',
                'password',
                'auth',
                $client->reveal()
            )
        );
    }

    public function testGetUserEntityByCredentialsReturnsNullIfPasswordVerificationFails()
    {
        $statement = $this->prophesize(PDOStatement::class);
        $statement->bindParam(':username', 'username')->shouldBeCalled();
        $statement->execute()->will(function () use ($statement): bool {
            $statement->fetch()->willReturn([
                'password' => 'not-the-same-password',
            ]);
            return true;
        });

        $this->pdo
            ->prepare(Argument::containingString('SELECT password FROM oauth_users'))
            ->will([$statement, 'reveal']);

        $client = $this->prophesize(ClientEntityInterface::class);

        $this->assertNull(
            $this->repo->getUserEntityByUserCredentials(
                'username',
                'password',
                'auth',
                $client->reveal()
            )
        );
    }

    public function testGetUserEntityByCredentialsReturnsNullIfUserIsNotFound()
    {
        $statement = $this->prophesize(PDOStatement::class);
        $statement->bindParam(':username', 'username')->shouldBeCalled();
        $statement->execute()->will(function () use ($statement): bool {
            $statement->fetch()->willReturn(null);
            return true;
        });

        $this->pdo
            ->prepare(Argument::containingString('SELECT password FROM oauth_users'))
            ->will([$statement, 'reveal']);

        $client = $this->prophesize(ClientEntityInterface::class);

        $this->assertNull(
            $this->repo->getUserEntityByUserCredentials(
                'username',
                'password',
                'auth',
                $client->reveal()
            )
        );
    }

    public function testGetUserEntityByCredentialsReturnsEntity()
    {
        $statement = $this->prophesize(PDOStatement::class);
        $statement->bindParam(':username', 'username')->shouldBeCalled();
        $statement->execute()->willReturn(true);
        $statement->fetch()->willReturn([
            'password' => password_hash('password', PASSWORD_DEFAULT),
        ]);

        $this->pdo
            ->prepare(Argument::containingString('SELECT password FROM oauth_users WHERE username = :username'))
            ->will([$statement, 'reveal']);

        $client = $this->prophesize(ClientEntityInterface::class);

        $entity = $this->repo->getUserEntityByUserCredentials(
            'username',
            'password',
            'auth',
            $client->reveal()
        );
        $this->assertInstanceOf(UserEntity::class, $entity);
        $this->assertEquals('username', $entity->getIdentifier());
    }
}
