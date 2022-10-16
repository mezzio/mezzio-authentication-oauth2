<?php

declare(strict_types=1);

namespace MezzioTest\Authentication\OAuth2\Entity;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class TimestampableTraitTest extends TestCase
{
    private TimestampableTraitStub $trait;

    protected function setUp(): void
    {
        $this->trait = new TimestampableTraitStub();
    }

    public function testCreatedAt(): void
    {
        $now = new DateTimeImmutable();
        $this->trait->setCreatedAt($now);
        $this->assertEquals($now, $this->trait->getCreatedAt());
    }

    public function testUpdatedAt(): void
    {
        $now = new DateTimeImmutable();
        $this->trait->setUpdatedAt($now);
        $this->assertEquals($now, $this->trait->getUpdatedAt());
    }

    public function testTimestampOnCreate(): void
    {
        $this->trait->timestampOnCreate();
        $this->assertNotEmpty($this->trait->getCreatedAt());
    }
}
