<?php

declare(strict_types=1);

namespace Mezzio\Authentication\OAuth2\Entity;

use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use ReturnTypeWillChange;

class ScopeEntity implements ScopeEntityInterface
{
    use EntityTrait;

    /**
     * @return mixed
     */
    #[ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->getIdentifier();
    }
}
