<?php

/**
 * @see       https://github.com/mezzio/mezzio-authentication-oauth2 for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-authentication-oauth2/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-authentication-oauth2/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Mezzio\Authentication\OAuth2\Entity;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;

use function method_exists;

trait TimestampableTrait
{
    /**
     * @var DateTime
     */
    protected $createdAt;

    /**
     * @var DateTime
     */
    protected $updatedAt;

    public function getCreatedAt() : DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeInterface $createdAt) : void
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt() : DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(DateTimeInterface $updatedAt) : void
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * Set createdAt on current date/time if not set, using
     * timezone if defined
     */
    public function timestampOnCreate() : void
    {
        if (! $this->createdAt) {
            if (method_exists($this, 'getTimezone')) {
                $this->createdAt = new DateTimeImmutable('now', new DateTimeZone($this->getTimezone()->getValue()));
            } else {
                $this->createdAt = new DateTimeImmutable();
            }
        }
    }
}
