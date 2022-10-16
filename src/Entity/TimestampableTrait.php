<?php

declare(strict_types=1);

namespace Mezzio\Authentication\OAuth2\Entity;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;

use function method_exists;

trait TimestampableTrait
{
    /** @var DateTimeInterface */
    protected $createdAt;

    /** @var DateTimeInterface */
    protected $updatedAt;

    public function getCreatedAt(): DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeInterface $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(DateTimeInterface $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * Set createdAt on current date/time if not set, using
     * timezone if defined
     */
    public function timestampOnCreate(): void
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
