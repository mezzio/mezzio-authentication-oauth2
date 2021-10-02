<?php

declare(strict_types=1);

namespace MezzioTest\Authentication\OAuth2\Entity;

use DateTimeImmutable;
use Mezzio\Authentication\OAuth2\Entity\TimestampableTrait;
use PHPUnit\Framework\TestCase;

class TimestampableTraitTest extends TestCase
{
    protected function setUp(): void
    {
        $this->trait = $this->getMockForTrait(TimestampableTrait::class);
    }

    public function testCreatedAt()
    {
        $now = new DateTimeImmutable();
        $this->trait->setCreatedAt($now);
        $this->assertEquals($now, $this->trait->getCreatedAt());
    }

    public function testUpdatedAt()
    {
        $now = new DateTimeImmutable();
        $this->trait->setUpdatedAt($now);
        $this->assertEquals($now, $this->trait->getUpdatedAt());
    }

    public function testTimestampOnCreate()
    {
        $this->trait->timestampOnCreate();
        $this->assertNotEmpty($this->trait->getCreatedAt());
    }
}
