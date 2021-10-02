<?php

declare(strict_types=1);

namespace Mezzio\Authentication\OAuth2\Repository\Pdo;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;
use Mezzio\Authentication\OAuth2\Entity\UserEntity;

use function password_verify;

class UserRepository extends AbstractRepository implements UserRepositoryInterface
{
    /**
     * @param string $username
     * @param string $password
     * @param string $grantType
     * @return UserEntity|void
     */
    public function getUserEntityByUserCredentials(
        $username,
        $password,
        $grantType,
        ClientEntityInterface $clientEntity
    ) {
        $sth = $this->pdo->prepare(
            'SELECT password FROM oauth_users WHERE username = :username'
        );
        $sth->bindParam(':username', $username);

        if (false === $sth->execute()) {
            return;
        }

        $row = $sth->fetch();

        if (! empty($row) && password_verify($password, $row['password'])) {
            return new UserEntity($username);
        }
    }
}
