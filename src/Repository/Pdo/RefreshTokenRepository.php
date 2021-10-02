<?php

declare(strict_types=1);

namespace Mezzio\Authentication\OAuth2\Repository\Pdo;

use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Exception\UniqueTokenIdentifierConstraintViolationException;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use Mezzio\Authentication\OAuth2\Entity\RefreshTokenEntity;

use function date;

class RefreshTokenRepository extends AbstractRepository implements RefreshTokenRepositoryInterface
{
    public function getNewRefreshToken(): RefreshTokenEntity
    {
        return new RefreshTokenEntity();
    }

    public function persistNewRefreshToken(RefreshTokenEntityInterface $refreshTokenEntity)
    {
        $sth = $this->pdo->prepare(
            'INSERT INTO oauth_refresh_tokens (id, access_token_id, revoked, expires_at) '
            . 'VALUES (:id, :access_token_id, :revoked, :expires_at)'
        );

        $sth->bindValue(':id', $refreshTokenEntity->getIdentifier());
        $sth->bindValue(':access_token_id', $refreshTokenEntity->getAccessToken()->getIdentifier());
        $sth->bindValue(':revoked', 0);
        $sth->bindValue(
            ':expires_at',
            date(
                'Y-m-d H:i:s',
                $refreshTokenEntity->getExpiryDateTime()->getTimestamp()
            )
        );

        if (false === $sth->execute()) {
            throw UniqueTokenIdentifierConstraintViolationException::create();
        }
    }

    /**
     * @param string $tokenId
     */
    public function revokeRefreshToken($tokenId)
    {
        $sth = $this->pdo->prepare(
            'UPDATE oauth_refresh_tokens SET revoked=:revoked WHERE id = :tokenId'
        );
        $sth->bindValue(':revoked', 1);
        $sth->bindParam(':tokenId', $tokenId);

        $sth->execute();
    }

    /**
     * @param string $tokenId
     */
    public function isRefreshTokenRevoked($tokenId): bool
    {
        $sth = $this->pdo->prepare(
            'SELECT revoked FROM oauth_refresh_tokens WHERE id = :tokenId'
        );
        $sth->bindParam(':tokenId', $tokenId);

        if (false === $sth->execute()) {
            return false;
        }
        $row = $sth->fetch();

        return isset($row['revoked']) ? (bool) $row['revoked'] : false;
    }
}
