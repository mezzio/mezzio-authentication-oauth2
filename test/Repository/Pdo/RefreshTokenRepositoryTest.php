<?php

declare(strict_types=1);

namespace MezzioTest\Authentication\OAuth2\Repository\Pdo;

use DateTime;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Exception\UniqueTokenIdentifierConstraintViolationException;
use Mezzio\Authentication\OAuth2\Entity\RefreshTokenEntity;
use Mezzio\Authentication\OAuth2\Repository\Pdo\PdoService;
use Mezzio\Authentication\OAuth2\Repository\Pdo\RefreshTokenRepository;
use PDOStatement;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

use Prophecy\PhpUnit\ProphecyTrait;
use function time;

class RefreshTokenRepositoryTest extends TestCase
{
    use ProphecyTrait;

    protected function setUp() : void
    {
        $this->pdo = $this->prophesize(PdoService::class);
        $this->repo = new RefreshTokenRepository($this->pdo->reveal());
    }

    public function testPersistNewRefreshTokenRaisesExceptionWhenStatementExecutionFails()
    {
        $accessToken = $this->prophesize(AccessTokenEntityInterface::class);
        $accessToken->getIdentifier()->willReturn('access_token_id');

        $time = time();
        $date = $this->prophesize(DateTime::class);
        $date->getTimestamp()->willReturn($time);

        $refreshToken = $this->prophesize(RefreshTokenEntityInterface::class);
        $refreshToken->getIdentifier()->willReturn('id');
        $refreshToken->getAccessToken()->will([$accessToken, 'reveal']);
        $refreshToken->getExpiryDateTime()->will([$date, 'reveal']);

        $statement = $this->prophesize(PDOStatement::class);
        $statement->bindValue(':id', 'id')->shouldBeCalled();
        $statement->bindValue(':access_token_id', 'access_token_id')->shouldBeCalled();
        $statement->bindValue(':revoked', 0)->shouldBeCalled();
        $statement->bindValue(':expires_at', date('Y-m-d H:i:s', $time))
            ->shouldBeCalled();
        $statement->execute()->willReturn(false)->shouldBeCalled();

        $this->pdo
            ->prepare(Argument::containingString('INSERT INTO oauth_refresh_tokens'))
            ->will([$statement, 'reveal']);

        $this->expectException(UniqueTokenIdentifierConstraintViolationException::class);
        $this->repo->persistNewRefreshToken($refreshToken->reveal());
    }

    public function testIsRefreshTokenRevokedReturnsFalseWhenStatementFailsExecution()
    {
        $statement = $this->prophesize(PDOStatement::class);
        $statement->bindParam(':tokenId', 'token_id')->shouldBeCalled();
        $statement->execute()->willReturn(false)->shouldBeCalled();
        $statement->fetch()->shouldNotBeCalled();

        $this->pdo
            ->prepare(Argument::containingString('SELECT revoked FROM oauth_refresh_tokens'))
            ->will([$statement, 'reveal']);

        $this->assertFalse($this->repo->isRefreshTokenRevoked('token_id'));
    }

    public function testIsRefreshTokenRevokedReturnsTrue()
    {
        $statement = $this->prophesize(PDOStatement::class);
        $statement->bindParam(':tokenId', 'token_id')->shouldBeCalled();
        $statement->execute()->willReturn(true)->shouldBeCalled();
        $statement->fetch()->willReturn(['revoked' => true]);

        $this->pdo
            ->prepare(Argument::containingString('SELECT revoked FROM oauth_refresh_tokens'))
            ->will([$statement, 'reveal']);

        $this->assertTrue($this->repo->isRefreshTokenRevoked('token_id'));
    }

    public function testGetNewRefreshToken()
    {
        $result = $this->repo->getNewRefreshToken();
        $this->assertInstanceOf(RefreshTokenEntity::class, $result);
    }

    public function testRevokeRefreshToken()
    {
        $statement = $this->prophesize(PDOStatement::class);
        $statement->bindParam(':tokenId', 'token_id')->shouldBeCalled();
        $statement->bindValue(':revoked', 1)->shouldBeCalled();
        $statement->execute()->willReturn(true)->shouldBeCalled();
        $statement->fetch()->shouldNotBeCalled();

        $this->pdo
            ->prepare(Argument::containingString(
                'UPDATE oauth_refresh_tokens SET revoked=:revoked WHERE id = :tokenId'
            ))
            ->will([$statement, 'reveal']);

        $this->repo->revokeRefreshToken('token_id');
    }
}
