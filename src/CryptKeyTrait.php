<?php

declare(strict_types=1);

namespace Mezzio\Authentication\OAuth2;

use League\OAuth2\Server\CryptKey;

use function is_string;
use function sprintf;

trait CryptKeyTrait
{
    /**
     * @param array|string $keyConfig
     */
    protected function getCryptKey($keyConfig, string $configPath): CryptKey
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
