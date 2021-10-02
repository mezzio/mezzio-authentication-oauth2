<?php

declare(strict_types=1);

namespace Mezzio\Authentication\OAuth2\Repository\Pdo;

use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Exception\UniqueTokenIdentifierConstraintViolationException;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;
use Mezzio\Authentication\OAuth2\Entity\AuthCodeEntity;

use function date;

class AuthCodeRepository extends AbstractRepository implements AuthCodeRepositoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function getNewAuthCode()
    {
        return new AuthCodeEntity();
    }

    /**
     * {@inheritDoc}
     */
    public function persistNewAuthCode(AuthCodeEntityInterface $authCodeEntity)
    {
        $sth = $this->pdo->prepare(
            'INSERT INTO oauth_auth_codes (id, user_id, client_id, scopes, revoked, expires_at) '
            . 'VALUES (:id, :user_id, :client_id, :scopes, :revoked, :expires_at)'
        );

        $sth->bindValue(':id', $authCodeEntity->getIdentifier());
        $sth->bindValue(':user_id', $authCodeEntity->getUserIdentifier());
        $sth->bindValue(':client_id', $authCodeEntity->getClient()->getIdentifier());
        $sth->bindValue(':scopes', $this->scopesToString($authCodeEntity->getScopes()));
        $sth->bindValue(':revoked', 0);
        $sth->bindValue(
            ':expires_at',
            date(
                'Y-m-d H:i:s',
                $authCodeEntity->getExpiryDateTime()->getTimestamp()
            )
        );

        if (false === $sth->execute()) {
            throw UniqueTokenIdentifierConstraintViolationException::create();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function revokeAuthCode($codeId)
    {
        $sth = $this->pdo->prepare(
            'UPDATE oauth_auth_codes SET revoked=:revoked WHERE id = :codeId'
        );
        $sth->bindValue(':revoked', 1);
        $sth->bindParam(':codeId', $codeId);

        $sth->execute();
    }

    /**
     * {@inheritDoc}
     */
    public function isAuthCodeRevoked($codeId)
    {
        $sth = $this->pdo->prepare(
            'SELECT revoked FROM oauth_auth_codes WHERE id = :codeId'
        );
        $sth->bindParam(':codeId', $codeId);

        if (false === $sth->execute()) {
            return false;
        }
        $row = $sth->fetch();

        return isset($row['revoked']) ? (bool) $row['revoked'] : false;
    }
}
