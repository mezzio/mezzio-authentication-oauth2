<?php

/**
 * @see       https://github.com/mezzio/mezzio-authentication-oauth2 for the canonical source repository
 */

declare(strict_types=1);

namespace MezzioTest\Authentication\OAuth2\Repository\Pdo;

use DateTime;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Exception\UniqueTokenIdentifierConstraintViolationException;
use Mezzio\Authentication\OAuth2\Entity\AccessTokenEntity;
use Mezzio\Authentication\OAuth2\Repository\Pdo\AccessTokenRepository;
use Mezzio\Authentication\OAuth2\Repository\Pdo\PdoService;
use PDOStatement;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;

use function date;
use function time;

class AccessTokenRepositoryTest extends TestCase
{
    use ProphecyTrait;

    /** @var AccessTokenRepository */
    private $repo;

    protected function setUp(): void
    {
        $this->pdo  = $this->prophesize(PdoService::class);
        $this->repo = new AccessTokenRepository($this->pdo->reveal());
    }

    public function testPersistNewAccessTokenRaisesExceptionWhenStatementExecutionFails()
    {
        $client = $this->prophesize(ClientEntityInterface::class);
        $client->getIdentifier()->willReturn('client_id');

        $scope = $this->prophesize(ScopeEntityInterface::class);
        $scope->getIdentifier()->willReturn('authentication');

        $time = time();
        $date = $this->prophesize(DateTime::class);
        $date->getTimestamp()->willReturn($time);

        $accessToken = $this->prophesize(AccessTokenEntityInterface::class);
        $accessToken->getIdentifier()->willReturn('id');
        $accessToken->getUserIdentifier()->willReturn('user_id');
        $accessToken->getClient()->will([$client, 'reveal']);
        $accessToken->getScopes()->willReturn([$scope->reveal()]);
        $accessToken->getExpiryDateTime()->will([$date, 'reveal']);

        $statement = $this->prophesize(PDOStatement::class);
        $statement
            ->execute([
                ':id'         => 'id',
                ':user_id'    => 'user_id',
                ':client_id'  => 'client_id',
                ':scopes'     => 'authentication',
                ':revoked'    => 0,
                ':expires_at' => date('Y-m-d H:i:s', $time),
            ])
            ->willReturn(false);

        $this->pdo
            ->prepare(Argument::containingString('INSERT INTO oauth_access_tokens'))
            ->will([$statement, 'reveal']);

        $this->expectException(UniqueTokenIdentifierConstraintViolationException::class);
        $this->repo->persistNewAccessToken($accessToken->reveal());
    }

    public function testIsAccessTokenRevokedReturnsFalseWhenStatementFailsExecution()
    {
        $statement = $this->prophesize(PDOStatement::class);
        $statement->bindParam(':tokenId', 'token_id')->shouldBeCalled();
        $statement->execute()->willReturn(false)->shouldBeCalled();
        $statement->fetch()->shouldNotBeCalled();

        $this->pdo
            ->prepare(Argument::containingString('SELECT revoked FROM oauth_access_tokens'))
            ->will([$statement, 'reveal']);

        $this->assertFalse($this->repo->isAccessTokenRevoked('token_id'));
    }

    public function testIsAccessTokenRevokedReturnsFalseWhenRowDoesNotContainRevokedFlag()
    {
        $statement = $this->prophesize(PDOStatement::class);
        $statement->bindParam(':tokenId', 'token_id')->shouldBeCalled();
        $statement->execute()->willReturn(null)->shouldBeCalled();
        $statement->fetch()->willReturn([])->shouldBeCalled();

        $this->pdo
            ->prepare(Argument::containingString('SELECT revoked FROM oauth_access_tokens'))
            ->will([$statement, 'reveal']);

        $this->assertFalse($this->repo->isAccessTokenRevoked('token_id'));
    }

    public function testIsAccessTokenRevokedReturnsFalseWhenRowRevokedFlagIsFalse()
    {
        $statement = $this->prophesize(PDOStatement::class);
        $statement->bindParam(':tokenId', 'token_id')->shouldBeCalled();
        $statement->execute()->willReturn(null)->shouldBeCalled();
        $statement->fetch()->willReturn(['revoked' => 0])->shouldBeCalled();

        $this->pdo
            ->prepare(Argument::containingString('SELECT revoked FROM oauth_access_tokens'))
            ->will([$statement, 'reveal']);

        $this->assertFalse($this->repo->isAccessTokenRevoked('token_id'));
    }

    public function testIsAccessTokenRevokedReturnsTrueWhenRowRevokedFlagIsTrue()
    {
        $statement = $this->prophesize(PDOStatement::class);
        $statement->bindParam(':tokenId', 'token_id')->shouldBeCalled();
        $statement->execute()->willReturn(null)->shouldBeCalled();
        $statement->fetch()->willReturn(['revoked' => 1])->shouldBeCalled();

        $this->pdo
            ->prepare(Argument::containingString('SELECT revoked FROM oauth_access_tokens'))
            ->will([$statement, 'reveal']);

        $this->assertTrue($this->repo->isAccessTokenRevoked('token_id'));
    }

    public function testIsAcessTokenRevokedRaisesExceptionWhenTokenIdDontExists()
    {
        $statement = $this->prophesize(PDOStatement::class);
        $statement->bindParam(':tokenId', 'token_id')->shouldBeCalled();
        $statement->execute()->willReturn(true)->shouldBeCalled();
        $statement->fetch()->willReturn(false)->shouldBeCalled();

        $this->pdo
            ->prepare(Argument::containingString('SELECT revoked FROM oauth_access_tokens'))
            ->will([$statement, 'reveal']);

        $this->expectException(OAuthServerException::class);
        $this->repo->isAccessTokenRevoked('token_id');
    }

    public function testRevokeAccessToken()
    {
        $statement = $this->prophesize(PDOStatement::class);
        $statement->bindParam(':tokenId', 'token_id')->shouldBeCalled();
        $statement->bindValue(':revoked', 1)->shouldBeCalled();
        $statement->execute()->willReturn(null)->shouldBeCalled();

        $this->pdo
            ->prepare(Argument::containingString('UPDATE oauth_access_tokens SET revoked=:revoked'))
            ->will([$statement, 'reveal']);

        $this->repo->revokeAccessToken('token_id');
    }

    public function testGetNewToken()
    {
        $client      = $this->prophesize(ClientEntityInterface::class)->reveal();
        $accessToken = $this->repo->getNewToken($client, []);
        $this->assertInstanceOf(AccessTokenEntity::class, $accessToken);
        $this->assertEquals($client, $accessToken->getClient());
        $this->assertEquals([], $accessToken->getScopes());
    }

    public function testGetNewTokenWithScopeAndIndentifier()
    {
        $client         = $this->prophesize(ClientEntityInterface::class)->reveal();
        $scopes         = [$this->prophesize(ScopeEntityInterface::class)->reveal()];
        $userIdentifier = 'foo';

        $accessToken = $this->repo->getNewToken($client, $scopes, $userIdentifier);
        $this->assertInstanceOf(AccessTokenEntity::class, $accessToken);
        $this->assertEquals($client, $accessToken->getClient());
        $this->assertEquals($scopes, $accessToken->getScopes());
        $this->assertEquals($userIdentifier, $accessToken->getUserIdentifier());
    }
}
