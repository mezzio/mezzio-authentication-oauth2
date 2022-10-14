<?php

declare(strict_types=1);

namespace MezzioTest\Authentication\OAuth2\Repository\Pdo;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use Mezzio\Authentication\OAuth2\Repository\Pdo\ClientRepository;
use Mezzio\Authentication\OAuth2\Repository\Pdo\PdoService;
use PDOStatement;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class ClientRepositoryTest extends TestCase
{
    use ProphecyTrait;

    private ObjectProphecy $pdo;
    private ClientRepository $repo;

    protected function setUp(): void
    {
        $this->pdo  = $this->prophesize(PdoService::class);
        $this->repo = new ClientRepository($this->pdo->reveal());
    }

    public function testGetClientEntityReturnsNullIfStatementExecutionReturnsFalse(): void
    {
        $statement = $this->prophesize(PDOStatement::class);
        $statement->bindParam(':clientIdentifier', 'client_id')->shouldBeCalled();
        $statement->execute()->willReturn(false);

        $this->pdo
            ->prepare(Argument::containingString('SELECT * FROM oauth_clients'))
            ->will([$statement, 'reveal']);

        $this->assertNull(
            $this->repo->getClientEntity('client_id')
        );
    }

    public function testGetClientEntityReturnsNullIfNoRowReturned(): void
    {
        $statement = $this->prophesize(PDOStatement::class);
        $statement->bindParam(':clientIdentifier', 'client_id')->shouldBeCalled();
        $statement->execute()->will(function () use ($statement): bool {
            $statement->fetch()->willReturn([]);
            return true;
        });

        $this->pdo
            ->prepare(Argument::containingString('SELECT * FROM oauth_clients'))
            ->will([$statement, 'reveal']);

        $this->assertNull(
            $this->repo->getClientEntity('client_id')
        );
    }

    public function testGetClientEntityReturnsCorrectEntity(): void
    {
        $name     = 'foo';
        $redirect = 'bar';

        $statement = $this->prophesize(PDOStatement::class);
        $statement->bindParam(':clientIdentifier', 'client_id')->shouldBeCalled();
        $statement->execute()->will(function () use ($statement, $name, $redirect): bool {
            $statement->fetch()->willReturn([
                'name'     => $name,
                'redirect' => $redirect,
            ]);
            return true;
        });

        $this->pdo
            ->prepare(Argument::containingString('SELECT * FROM oauth_clients'))
            ->will([$statement, 'reveal']);

        $this->prophesize(ClientEntityInterface::class);

        /** @var ClientEntityInterface $client */
        $client = $this->repo->getClientEntity('client_id');

        $this->assertInstanceOf(
            ClientEntityInterface::class,
            $client
        );
        $this->assertEquals(
            $name,
            $client->getName()
        );
        $this->assertEquals(
            [$redirect],
            $client->getRedirectUri()
        );
    }

    public function invalidGrants(): array
    {
        return [
            'personal_access_password_mismatch' => [
                'authorization_code',
                [
                    'personal_access_client' => 'personal',
                    'password_client'        => 'password',
                ],
            ],
            'personal_access_revoked'           => [
                'personal_access',
                [
                    'personal_access_client' => false,
                ],
            ],
            'password_revoked'                  => [
                'password',
                [
                    'password_client' => false,
                ],
            ],
        ];
    }

    public function testValidateClientReturnsFalseIfNoRowReturned(): void
    {
        $statement = $this->prophesize(PDOStatement::class);
        $statement->bindParam(':clientIdentifier', 'client_id')->shouldBeCalled();
        $statement->execute()->will(function () use ($statement): bool {
            $statement->fetch()->willReturn([]);
            return true;
        });

        $this->pdo
            ->prepare(Argument::containingString('SELECT * FROM oauth_clients'))
            ->will([$statement, 'reveal']);

        $client = $this->prophesize(ClientEntityInterface::class);

        $this->assertFalse(
            $this->repo->validateClient(
                'client_id',
                '',
                'password'
            )
        );
    }

    /**
     * @dataProvider invalidGrants
     */
    public function testValidateClientReturnsFalseIfRowIndicatesNotGranted(string $grantType, array $rowReturned): void
    {
        $statement = $this->prophesize(PDOStatement::class);
        $statement->bindParam(':clientIdentifier', 'client_id')->shouldBeCalled();
        $statement->execute()->will(function () use ($statement, $rowReturned): bool {
            $statement->fetch()->willReturn($rowReturned);
            return true;
        });

        $this->pdo
            ->prepare(Argument::containingString('SELECT * FROM oauth_clients'))
            ->will([$statement, 'reveal']);

        $this->assertFalse(
            $this->repo->validateClient(
                'client_id',
                '',
                $grantType
            )
        );
    }

    public function testValidateClientReturnsFalseForNonMatchingClientSecret(): void
    {
        $statement = $this->prophesize(PDOStatement::class);
        $statement->bindParam(':clientIdentifier', 'client_id')->shouldBeCalled();
        $statement->execute()->will(function () use ($statement): bool {
            $statement->fetch()->willReturn([
                'password_client' => true,
                'secret'          => 'bar',
            ]);
            return true;
        });

        $this->pdo
            ->prepare(Argument::containingString('SELECT * FROM oauth_clients'))
            ->will([$statement, 'reveal']);

        $client = $this->prophesize(ClientEntityInterface::class);

        $this->assertFalse(
            $this->repo->validateClient(
                'client_id',
                'foo',
                'password'
            )
        );
    }

    public function testValidateClientReturnsFalseForEmptyClientSecret(): void
    {
        $statement = $this->prophesize(PDOStatement::class);
        $statement->bindParam(':clientIdentifier', 'client_id')->shouldBeCalled();
        $statement->execute()->will(function () use ($statement): bool {
            $statement->fetch()->willReturn([
                'password_client' => true,
                'secret'          => null,
            ]);
            return true;
        });

        $this->pdo
            ->prepare(Argument::containingString('SELECT * FROM oauth_clients'))
            ->will([$statement, 'reveal']);

        $client = $this->prophesize(ClientEntityInterface::class);

        $this->assertFalse(
            $this->repo->validateClient(
                'client_id',
                'foo',
                'password'
            )
        );
    }
}
