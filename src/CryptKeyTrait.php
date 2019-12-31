<?php

/**
 * @see       https://github.com/mezzio/mezzio-authentication-oauth2 for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-authentication-oauth2/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-authentication-oauth2/blob/master/LICENSE.md New BSD License
 */

namespace Mezzio\Authentication\OAuth2;

use League\OAuth2\Server\CryptKey;

use function is_string;
use function sprintf;

trait CryptKeyTrait
{
    protected function getCryptKey($keyConfig, string $configPath) : CryptKey
    {
        if (is_string($keyConfig)) {
            return new CryptKey($keyConfig);
        }

        if (! isset($keyConfig['key_or_path'])) {
            throw new Exception\InvalidConfigException(
                sprintf('The key_or_path value is missing in config %s', $configPath)
            );
        }

        $passPhrase = $keyConfig['pass_phrase'] ?? null;

        if (isset($keyConfig['key_permissions_check'])) {
            return new CryptKey($keyConfig['key_or_path'], $passPhrase, (bool) $keyConfig['key_permissions_check']);
        }

        return new CryptKey($keyConfig['key_or_path'], $passPhrase);
    }
}
