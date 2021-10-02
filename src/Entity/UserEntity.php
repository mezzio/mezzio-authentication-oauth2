<?php

declare(strict_types=1);

namespace Mezzio\Authentication\OAuth2\Entity;

use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\UserEntityInterface;

class UserEntity implements UserEntityInterface
{
    use EntityTrait;

    /**
     * Create a new user instance.
     *
     * @param  string|int  $identifier
     * @return void
     */
    public function __construct($identifier)
    {
        $this->setIdentifier($identifier);
    }
}
