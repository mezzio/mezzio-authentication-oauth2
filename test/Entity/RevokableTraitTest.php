<?php

declare(strict_types=1);

namespace MezzioTest\Authentication\OAuth2\Entity;

use PHPUnit\Framework\TestCase;

class RevokableTraitTest extends TestCase
{
    private RevokableTraitStub $trait;

    protected function setUp(): void
    {
        $this->trait = new RevokableTraitStub();
    }

    public function testSetRevokedToTrue(): void
    {
        $this->trait->setRevoked(true);
        $this->assertTrue($this->trait->isRevoked());
    }

    public function testSetRevokedToFalse(): void
    {
        $this->trait->setRevoked(false);
        $this->assertFalse($this->trait->isRevoked());
    }
}
