<?php

declare(strict_types=1);

namespace Mezzio\Authentication\OAuth2\Repository\Pdo;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;
use Mezzio\Authentication\OAuth2\Entity\ScopeEntity;

class ScopeRepository extends AbstractRepository implements ScopeRepositoryInterface
{
    /**
     * @param string $identifier
     * @return ScopeEntity|void
     */
    public function getScopeEntityByIdentifier($identifier)
    {
        $sth = $this->pdo->prepare(
            'SELECT id FROM oauth_scopes WHERE id = :identifier'
        );
        $sth->bindParam(':identifier', $identifier);

        if (false === $sth->execute()) {
            return;
        }

        $row = $sth->fetch();
        if (! isset($row['id'])) {
            return;
        }

        $scope = new ScopeEntity();
        $scope->setIdentifier($row['id']);
        return $scope;
    }

    /**
     * @param ScopeEntityInterface[] $scopes
     * @param string                 $grantType
     * @param null|string            $userIdentifier
     * @return ScopeEntityInterface[]
     */
    public function finalizeScopes(
        array $scopes,
        $grantType,
        ClientEntityInterface $clientEntity,
        $userIdentifier = null
    ): array {
        return $scopes;
    }
}
