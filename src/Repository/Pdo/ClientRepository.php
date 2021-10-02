<?php

declare(strict_types=1);

namespace Mezzio\Authentication\OAuth2\Repository\Pdo;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use Mezzio\Authentication\OAuth2\Entity\ClientEntity;

use function password_verify;

class ClientRepository extends AbstractRepository implements ClientRepositoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function getClientEntity($clientIdentifier): ?ClientEntityInterface
    {
        $clientData = $this->getClientData($clientIdentifier);

        if (empty($clientData)) {
            return null;
        }

        return new ClientEntity(
            $clientIdentifier,
            $clientData['name'] ?? '',
            $clientData['redirect'] ?? '',
            (bool) ($clientData['is_confidential'] ?? null)
        );
    }

    /**
     * {@inheritDoc}
     */
    public function validateClient($clientIdentifier, $clientSecret, $grantType): bool
    {
        $clientData = $this->getClientData($clientIdentifier);

        if (empty($clientData)) {
            return false;
        }

        if (! $this->isGranted($clientData, $grantType)) {
            return false;
        }

        if (empty($clientData['secret']) || ! password_verify((string) $clientSecret, $clientData['secret'])) {
            return false;
        }

        return true;
    }

    /**
     * Check the grantType for the client value, stored in $row
     *
     * @param array  $row
     */
    protected function isGranted(array $row, ?string $grantType = null): bool
    {
        switch ($grantType) {
            case 'authorization_code':
                return ! ($row['personal_access_client'] || $row['password_client']);
            case 'personal_access':
                return (bool) $row['personal_access_client'];
            case 'password':
                return (bool) $row['password_client'];
            default:
                return true;
        }
    }

    private function getClientData(string $clientIdentifier): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT * FROM oauth_clients WHERE name = :clientIdentifier'
        );
        $statement->bindParam(':clientIdentifier', $clientIdentifier);

        if ($statement->execute() === false) {
            return null;
        }

        $row = $statement->fetch();

        if (empty($row)) {
            return null;
        }

        return $row;
    }
}
