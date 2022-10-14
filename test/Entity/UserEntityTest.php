<?php

declare(strict_types=1);

namespace MezzioTest\Authentication\OAuth2\Entity;

use ArgumentCountError;
use League\OAuth2\Server\Entities\UserEntityInterface;
use Mezzio\Authentication\OAuth2\Entity\UserEntity;
use PHPUnit\Framework\TestCase;

class UserEntityTest extends TestCase
{
    private UserEntity $entity;

    protected function setUp(): void
    {
        $this->entity = new UserEntity('foo');
    }

    public function testConstructorWithoutParamWillResultInAnException(): void
    {
        $this->expectException(ArgumentCountError::class);
        $entity = new UserEntity();
    }

    public function testImplementsUserEntityInterface(): void
    {
        $this->assertInstanceOf(UserEntityInterface::class, $this->entity);
    }

    public function testGetIdentifier(): void
    {
        $this->assertEquals('foo', $this->entity->getIdentifier());
    }
}
