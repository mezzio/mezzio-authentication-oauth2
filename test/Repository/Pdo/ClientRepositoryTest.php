<?php

declare(strict_types=1);

namespace MezzioTest\Authentication\OAuth2\Repository\Pdo;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use Mezzio\Authentication\OAuth2\Repository\Pdo\ClientRepository;
use Mezzio\Authentication\OAuth2\Repository\Pdo\PdoService;
use PDOStatement;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ClientRepositoryTest extends TestCase
{
    /** @var PdoService&MockObject */
    private PdoService $pdo;
    private ClientRepository $repo;

    protected function setUp(): void
    {
        $this->pdo  = $this->createMock(PdoService::class);
        $this->repo = new ClientRepository($this->pdo);
    }

    public function testGetClientEntityReturnsNullIfStatementExecutionReturnsFalse(): void
    {
        $statement = $this->createMock(PDOStatement::class);
        $statement->expects(self::once())
            ->method('bindParam')
            ->with(':clientIdentifier', 'client_id');
        $statement->expects(self::once())
            ->method('execute')
            ->willReturn(false);

        $this->pdo->expects(self::once())
            ->method('prepare')
            ->with(self::stringContains('SELECT * FROM oauth_clients'))
            ->willReturn($statement);

        self::assertNull(
            $this->repo->getClientEntity('client_id')
        );
    }

    public function testGetClientEntityReturnsNullIfNoRowReturned(): void
    {
        $statement = $this->createMock(PDOStatement::class);
        $statement->expects(self::once())
            ->method('bindParam')
            ->with(':clientIdentifier', 'client_id');
        $statement->expects(self::once())
            ->method('execute')
            ->willReturn(true);
        $statement->expects(self::once())
            ->method('fetch')
            ->willReturn([]);

        $this->pdo->expects(self::once())
            ->method('prepare')
            ->with(self::stringContains('SELECT * FROM oauth_clients'))
            ->willReturn($statement);

        self::assertNull(
            $this->repo->getClientEntity('client_id')
        );
    }

    public function testGetClientEntityReturnsCorrectEntity(): void
    {
        $name     = 'foo';
        $redirect = 'bar';

        $statement = $this->createMock(PDOStatement::class);
        $statement->expects(self::once())
            ->method('bindParam')
            ->with(':clientIdentifier', 'client_id');
        $statement->expects(self::once())
            ->method('execute')
            ->willReturn(true);
        $statement->expects(self::once())
            ->method('fetch')
            ->willReturn([
                'name'     => $name,
                'redirect' => $redirect,
            ]);

        $this->pdo->expects(self::once())
            ->method('prepare')
            ->with(self::stringContains('SELECT * FROM oauth_clients'))
            ->willReturn($statement);

        /** @var ClientEntityInterface $client */
        $client = $this->repo->getClientEntity('client_id');

        self::assertInstanceOf(
            ClientEntityInterface::class,
            $client
        );
        self::assertEquals(
            $name,
            $client->getName()
        );
        self::assertEquals(
            [$redirect],
            $client->getRedirectUri()
        );
    }

    /** @return array<string, array{0: string, 1: array}> */
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
        $statement = $this->createMock(PDOStatement::class);
        $statement->expects(self::once())
            ->method('bindParam')
            ->with(':clientIdentifier', 'client_id');
        $statement->expects(self::once())
            ->method('execute')
            ->willReturn(true);
        $statement->expects(self::once())
            ->method('fetch')
            ->willReturn([]);

        $this->pdo->expects(self::once())
            ->method('prepare')
            ->with(self::stringContains('SELECT * FROM oauth_clients'))
            ->willReturn($statement);

        self::assertFalse(
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
        $statement = $this->createMock(PDOStatement::class);
        $statement->expects(self::once())
            ->method('bindParam')
            ->with(':clientIdentifier', 'client_id');
        $statement->expects(self::once())
            ->method('execute')
            ->willReturn(true);
        $statement->expects(self::once())
            ->method('fetch')
            ->willReturn($rowReturned);

        $this->pdo->expects(self::once())
            ->method('prepare')
            ->with(self::stringContains('SELECT * FROM oauth_clients'))
            ->willReturn($statement);

        self::assertFalse(
            $this->repo->validateClient(
                'client_id',
                '',
                $grantType
            )
        );
    }

    public function testValidateClientReturnsFalseForNonMatchingClientSecret(): void
    {
        $statement = $this->createMock(PDOStatement::class);
        $statement->expects(self::once())
            ->method('bindParam')
            ->with(':clientIdentifier', 'client_id');
        $statement->expects(self::once())
            ->method('execute')
            ->willReturn(true);
        $statement->expects(self::once())
            ->method('fetch')
            ->willReturn([
                'password_client' => true,
                'secret'          => 'bar',
            ]);

        $this->pdo->expects(self::once())
            ->method('prepare')
            ->with(self::stringContains('SELECT * FROM oauth_clients'))
            ->willReturn($statement);

        self::assertFalse(
            $this->repo->validateClient(
                'client_id',
                'foo',
                'password'
            )
        );
    }

    public function testValidateClientReturnsFalseForEmptyClientSecret(): void
    {
        $statement = $this->createMock(PDOStatement::class);
        $statement->expects(self::once())
            ->method('bindParam')
            ->with(':clientIdentifier', 'client_id');
        $statement->expects(self::once())
            ->method('execute')
            ->willReturn(true);
        $statement->expects(self::once())
            ->method('fetch')
            ->willReturn([
                'password_client' => true,
                'secret'          => null,
            ]);

        $this->pdo->expects(self::once())
            ->method('prepare')
            ->with(self::stringContains('SELECT * FROM oauth_clients'))
            ->willReturn($statement);

        self::assertFalse(
            $this->repo->validateClient(
                'client_id',
                'foo',
                'password'
            )
        );
    }
}
