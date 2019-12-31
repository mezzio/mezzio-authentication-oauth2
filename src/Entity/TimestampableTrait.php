<?php

/**
 * @see       https://github.com/mezzio/mezzio-authentication-oauth2 for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-authentication-oauth2/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-authentication-oauth2/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Mezzio\Authentication\OAuth2\Entity;

use DateTime;
use DateTimeZone;

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

    public function getCreatedAt() : DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTime $createdAt) : void
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt() : DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(DateTime $updatedAt) : void
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
            $this->createdAt = new DateTime();
            if (method_exists($this, 'getTimezone')) {
                $this->createdAt->setTimezone(new DateTimeZone($this->getTimezone()->getValue()));
            }
        }
    }
}
