<?php

/**
 * @see       https://github.com/mezzio/mezzio-authentication-oauth2 for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-authentication-oauth2/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-authentication-oauth2/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace MezzioTest\Authentication\OAuth2\Entity;

use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use Mezzio\Authentication\OAuth2\Entity\AccessTokenEntity;
use PHPUnit\Framework\TestCase;

class AccessTokenEntityTest extends TestCase
{
    public function testImplementsInstanceAccessTokenEntityInterface()
    {
        $entity = new AccessTokenEntity();
        $this->assertInstanceOf(AccessTokenEntityInterface::class, $entity);
    }
}
