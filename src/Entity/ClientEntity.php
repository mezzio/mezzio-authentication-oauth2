<?php

/**
 * @see       https://github.com/mezzio/mezzio-authentication-oauth2 for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-authentication-oauth2/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-authentication-oauth2/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Mezzio\Authentication\OAuth2\Entity;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\Traits\ClientTrait;
use League\OAuth2\Server\Entities\Traits\EntityTrait;

use function explode;

class ClientEntity implements ClientEntityInterface
{
    use ClientTrait, EntityTrait, RevokableTrait, TimestampableTrait;

    /**
     * @var string
     */
    protected $secret;

    /**
     * @var bool
     */
    protected $personalAccessClient;

    /**
     * @var bool
     */
    protected $passwordClient;


    /**
     * Constructor
     *
     * @param string $identifier
     * @param string $name
     * @param string $redirectUri
     * @param bool   $isConfidential
     * @return void
     */
    public function __construct(string $identifier, string $name, string $redirectUri, bool $isConfidential = false)
    {
        $this->setIdentifier($identifier);
        $this->name = $name;
        $this->redirectUri = explode(',', $redirectUri);
        $this->isConfidential = $isConfidential;
    }

    public function getSecret(): string
    {
        return $this->secret;
    }

    public function setSecret(string $secret): void
    {
        $this->secret = $secret;
    }

    public function hasPersonalAccessClient(): bool
    {
        return $this->personalAccessClient;
    }

    public function setPersonalAccessClient(bool $personalAccessClient): void
    {
        $this->personalAccessClient = $personalAccessClient;
    }

    public function hasPasswordClient(): bool
    {
        return $this->passwordClient;
    }

    public function setPasswordClient(bool $passwordClient): void
    {
        $this->passwordClient = $passwordClient;
    }
}
