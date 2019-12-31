<?php

/**
 * @see       https://github.com/mezzio/mezzio-authentication-oauth2 for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-authentication-oauth2/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-authentication-oauth2/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace MezzioTest\Authentication\OAuth2\Entity;

use ArgumentCountError;
use League\OAuth2\Server\Entities\UserEntityInterface;
use Mezzio\Authentication\OAuth2\Entity\UserEntity;
use PHPUnit\Framework\TestCase;

class UserEntityTest extends TestCase
{
    /** @var UserEntity */
    private $entity;

    protected function setUp() : void
    {
        $this->entity = new UserEntity('foo');
    }

    public function testConstructorWithoutParamWillResultInAnException()
    {
        $this->expectException(ArgumentCountError::class);
        $entity = new UserEntity();
    }

    public function testImplementsUserEntityInterface()
    {
        $this->assertInstanceOf(UserEntityInterface::class, $this->entity);
    }

    public function testGetIdentifier()
    {
        $this->assertEquals('foo', $this->entity->getIdentifier());
    }
}
