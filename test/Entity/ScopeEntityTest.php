<?php

declare(strict_types=1);

namespace MezzioTest\Authentication\OAuth2\Entity;

use League\OAuth2\Server\Entities\ScopeEntityInterface;
use Mezzio\Authentication\OAuth2\Entity\ScopeEntity;
use PHPUnit\Framework\TestCase;

use function json_encode;

class ScopeEntityTest extends TestCase
{
    private ScopeEntity $entity;

    protected function setUp(): void
    {
        $this->entity = new ScopeEntity();
    }

    public function testImplementsScopeEntityInterface(): void
    {
        $this->assertInstanceOf(ScopeEntityInterface::class, $this->entity);
    }

    public function testEntityIsJsonSerializable(): void
    {
        $this->entity->setIdentifier('foo');
        $this->assertEquals('"foo"', json_encode($this->entity));
    }
}
