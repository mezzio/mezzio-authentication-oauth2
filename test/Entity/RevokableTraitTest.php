<?php

declare(strict_types=1);

namespace MezzioTest\Authentication\OAuth2\Entity;

use Mezzio\Authentication\OAuth2\Entity\RevokableTrait;
use PHPUnit\Framework\TestCase;

class RevokableTraitTest extends TestCase
{
    protected function setUp(): void
    {
        $this->trait = $this->getMockForTrait(RevokableTrait::class);
    }

    public function testSetRevokedToTrue()
    {
        $this->trait->setRevoked(true);
        $this->assertTrue($this->trait->isRevoked());
    }

    public function testSetRevokedToFalse()
    {
        $this->trait->setRevoked(false);
        $this->assertFalse($this->trait->isRevoked());
    }
}
