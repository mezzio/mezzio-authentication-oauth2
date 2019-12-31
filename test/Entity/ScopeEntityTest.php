<?php

/**
 * @see       https://github.com/mezzio/mezzio-authentication-oauth2 for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-authentication-oauth2/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-authentication-oauth2/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace MezzioTest\Authentication\OAuth2\Entity;

use League\OAuth2\Server\Entities\ScopeEntityInterface;
use Mezzio\Authentication\OAuth2\Entity\ScopeEntity;
use PHPUnit\Framework\TestCase;

class ScopeEntityTest extends TestCase
{
    protected function setUp() : void
    {
        $this->entity = new ScopeEntity();
    }

    public function testImplementsScopeEntityInterface()
    {
        $this->assertInstanceOf(ScopeEntityInterface::class, $this->entity);
    }

    public function testEntityIsJsonSerializable()
    {
        $this->entity->setIdentifier('foo');
        $this->assertEquals('"foo"', json_encode($this->entity));
    }
}
