<?php

/**
 * @see       https://github.com/mezzio/mezzio-authentication-oauth2 for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-authentication-oauth2/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-authentication-oauth2/blob/master/LICENSE.md New BSD License
 */

// change the permission of private and public keys to 0600
chmod(__DIR__ . '/TestAsset/private.key', 0600);
chmod(__DIR__ . '/TestAsset/public.key', 0600);

require __DIR__ . '/../vendor/autoload.php';
