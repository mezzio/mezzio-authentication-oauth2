<?php

/**
 * @see       https://github.com/mezzio/mezzio-authentication-oauth2 for the canonical source repository
 */

declare(strict_types=1);

namespace Mezzio\Authentication\OAuth2\Entity;

trait RevokableTrait
{
    /** @var bool */
    protected $revoked;

    public function isRevoked(): bool
    {
        return $this->revoked;
    }

    public function setRevoked(bool $revoked): void
    {
        $this->revoked = $revoked;
    }
}
