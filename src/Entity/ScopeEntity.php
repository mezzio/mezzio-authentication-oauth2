<?php

declare(strict_types=1);

namespace Mezzio\Authentication\OAuth2\Entity;

use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Entities\Traits\EntityTrait;

/** @final */
class ScopeEntity implements ScopeEntityInterface
{
    use EntityTrait;

    public function jsonSerialize(): mixed
    {
        return $this->getIdentifier();
    }
}
