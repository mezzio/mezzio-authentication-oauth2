<?php

declare(strict_types=1);

namespace Mezzio\Authentication\OAuth2\Repository\Pdo;

use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Exception\UniqueTokenIdentifierConstraintViolationException;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use Mezzio\Authentication\OAuth2\Entity\AccessTokenEntity;

use function array_key_exists;
use function date;
use function implode;
use function is_array;
use function sprintf;

/** @final */
class AccessTokenRepository extends AbstractRepository implements AccessTokenRepositoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function getNewToken(
        ClientEntityInterface $clientEntity,
        array $scopes,
        string|null $userIdentifier = null
    ): AccessTokenEntityInterface {
        $accessToken = new AccessTokenEntity();
        $accessToken->setClient($clientEntity);
        foreach ($scopes as $scope) {
            $accessToken->addScope($scope);
        }
        if (null !== $userIdentifier && $userIdentifier !== '') {
            $accessToken->setUserIdentifier($userIdentifier);
        }
        return $accessToken;
    }

    /**
     * {@inheritDoc}
     */
    public function persistNewAccessToken(AccessTokenEntityInterface $accessTokenEntity): void
    {
        $columns = [
            'id',
            'user_id',
            'client_id',
            'scopes',
            'revoked',
            'created_at',
            'updated_at',
            'expires_at',
        ];

        $values = [
            ':id',
            ':user_id',
            ':client_id',
            ':scopes',
            ':revoked',
            'CURRENT_TIMESTAMP',
            'CURRENT_TIMESTAMP',
            ':expires_at',
        ];

        $sth = $this->pdo->prepare(sprintf(
            'INSERT INTO oauth_access_tokens (%s) VALUES (%s)',
            implode(', ', $columns),
            implode(', ', $values)
        ));

        $params = [
            ':id'         => $accessTokenEntity->getIdentifier(),
            ':user_id'    => $accessTokenEntity->getUserIdentifier(),
            ':client_id'  => $accessTokenEntity->getClient()->getIdentifier(),
            ':scopes'     => $this->scopesToString($accessTokenEntity->getScopes()),
            ':revoked'    => 0,
            ':expires_at' => date(
                'Y-m-d H:i:s',
                $accessTokenEntity->getExpiryDateTime()->getTimestamp()
            ),
        ];

        if (false === $sth->execute($params)) {
            throw UniqueTokenIdentifierConstraintViolationException::create();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function revokeAccessToken(string $tokenId): void
    {
        $sth = $this->pdo->prepare(
            'UPDATE oauth_access_tokens SET revoked=:revoked WHERE id = :tokenId'
        );
        $sth->bindValue(':revoked', 1);
        $sth->bindParam(':tokenId', $tokenId);

        $sth->execute();
    }

    /**
     * {@inheritDoc}
     */
    public function isAccessTokenRevoked(string $tokenId): bool
    {
        $sth = $this->pdo->prepare(
            'SELECT revoked FROM oauth_access_tokens WHERE id = :tokenId'
        );
        $sth->bindParam(':tokenId', $tokenId);

        if (false === $sth->execute()) {
            return false;
        }
        $row = $sth->fetch();
        if (! is_array($row)) {
            throw OAuthServerException::invalidRefreshToken();
        }

        return array_key_exists('revoked', $row) ? (bool) $row['revoked'] : false;
    }
}
