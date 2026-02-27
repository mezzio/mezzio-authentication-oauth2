<?php

declare(strict_types=1);

namespace Mezzio\Authentication\OAuth2\Repository\Pdo;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;
use Mezzio\Authentication\OAuth2\Entity\ScopeEntity;

use function is_string;

/** @final */
class ScopeRepository extends AbstractRepository implements ScopeRepositoryInterface
{
    /**
     * @return ScopeEntity|void
     */
    public function getScopeEntityByIdentifier(string $identifier): ?ScopeEntityInterface
    {
        $sth = $this->pdo->prepare(
            'SELECT id FROM oauth_scopes WHERE id = :identifier'
        );
        $sth->bindValue(':identifier', $identifier);

        if (false === $sth->execute()) {
            return null;
        }

        $id = $sth->fetchColumn();
        if (! is_string($id) || $id === '') {
            return null;
        }

        $scope = new ScopeEntity();
        $scope->setIdentifier($id);
        return $scope;
    }

    /**
     * @param ScopeEntityInterface[] $scopes
     * @return ScopeEntityInterface[]
     */
    public function finalizeScopes(
        array $scopes,
        string $grantType,
        ClientEntityInterface $clientEntity,
        string|null $userIdentifier = null,
        ?string $authCodeId = null
    ): array {
        return $scopes;
    }
}
