<?php

/**
 * @see       https://github.com/mezzio/mezzio-authentication-oauth2 for the canonical source repository
 */

declare(strict_types=1);

// change the permission of private and public keys to 0600
chmod(__DIR__ . '/TestAsset/private.key', 0600);
chmod(__DIR__ . '/TestAsset/public.key', 0600);

require __DIR__ . '/../vendor/autoload.php';
