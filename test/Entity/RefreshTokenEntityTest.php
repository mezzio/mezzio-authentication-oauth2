<?php

declare(strict_types=1);

namespace MezzioTest\Authentication\OAuth2\Entity;

use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use Mezzio\Authentication\OAuth2\Entity\RefreshTokenEntity;
use PHPUnit\Framework\TestCase;

class RefreshTokenEntityTest extends TestCase
{
    public function testImplementsRefreshTokenEntityInterface()
    {
        $entity = new RefreshTokenEntity();
        $this->assertInstanceOf(RefreshTokenEntityInterface::class, $entity);
    }
}
