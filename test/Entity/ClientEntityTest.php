<?php

declare(strict_types=1);

namespace MezzioTest\Authentication\OAuth2\Entity;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use Mezzio\Authentication\OAuth2\Entity\ClientEntity;
use PHPUnit\Framework\TestCase;

class ClientEntityTest extends TestCase
{
    private ClientEntity $entity;

    protected function setUp(): void
    {
        $this->entity = new ClientEntity('foo', 'bar', 'http://localhost');
    }

    public function testImplementsAuthCodeEntityInterface(): void
    {
        $this->assertInstanceOf(ClientEntityInterface::class, $this->entity);
    }

    public function testConstructorSetsIdentifier(): void
    {
        $this->assertSame('foo', $this->entity->getIdentifier());
    }

    public function testConstructorSetsName(): void
    {
        $this->assertSame('bar', $this->entity->getName());
    }

    public function testConstructorSetsRedirectUri(): void
    {
        $this->assertSame(['http://localhost'], $this->entity->getRedirectUri());
    }

    public function testSecret(): void
    {
        $this->entity->setSecret('secret');
        $this->assertEquals('secret', $this->entity->getSecret());
    }

    public function testPersonalAccessClient(): void
    {
        $this->entity->setPersonalAccessClient(true);
        $this->assertTrue($this->entity->hasPersonalAccessClient());

        $this->entity->setPersonalAccessClient(false);
        $this->assertFalse($this->entity->hasPersonalAccessClient());
    }

    public function testPasswordClient(): void
    {
        $this->entity->setPasswordClient(true);
        $this->assertTrue($this->entity->hasPasswordClient());

        $this->entity->setPasswordClient(false);
        $this->assertFalse($this->entity->hasPasswordClient());
    }
}
