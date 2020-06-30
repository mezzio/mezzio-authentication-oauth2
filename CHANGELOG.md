# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 2.0.3 - 2020-06-30

### Added

- Nothing.

### Changed

- [#16](https://github.com/mezzio/mezzio-authentication-oauth2/pull/16) adds support for checking if an OAuth2 client is marked as "confidential". To make use of this feature, you will need to add the field `is_confidential` to your `oauth_clients` table as a TINYINT: `is_confidential tinyint(1) NOT NULL DEFAULT "0"`

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#16](https://github.com/mezzio/mezzio-authentication-oauth2/pull/16) fixes compatibility with the upstream league/oauth2-server library when performing a `ClientCredentialsCheck` grant.

## 2.0.2 - 2020-03-28

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Fixed `replace` version constraint in composer.json so repository can be used as replacement of `zendframework/zend-expressive-authentication-oauth2:^2.0.0`.

## 2.0.1 - 2020-02-10

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#12](https://github.com/mezzio/mezzio-authentication-oauth2/pull/12) fixes default paths to keys in configuration (uses `data/oauth` directories).

## 2.0.0 - 2019-12-28

### Added

- Nothing.

### Changed

- [zendframework/zend-expressive-authentication-oauth2#69](https://github.com/zendframework/zend-expressive-authentication-oauth2/pull/69) updates the minimum supported version of league/oauth-server to ^8.0.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 1.3.1 - 2020-02-10

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#12](https://github.com/mezzio/mezzio-authentication-oauth2/pull/12) fixes default paths to keys in configuration (uses `data/oauth` directories).

## 1.3.0 - 2019-12-28

### Added

- [zendframework/zend-expressive-authentication-oauth2#62](https://github.com/zendframework/zend-expressive-authentication-oauth2/pull/62) adds the ability to configure and add event listeners for the underlying league/oauth2 implementation. See the [event listeners configuration documentation](https://docs.mezzio.dev/mezzio-authentication-oauth2/intro/#configure-event-listeners) for more information.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 1.2.1 - 2019-12-28

### Added

- Nothing.

### Changed

- [zendframework/zend-expressive-authentication-oauth2#55](https://github.com/zendframework/zend-expressive-authentication-oauth2/pull/55) changes how the `OAuth2Adapter` validates when a client ID is present. Previously, if a client ID was present, but not a user ID, it would attempt to pull a user from the user factory using the client ID, which was incorrect. With this release, it no longer does that.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-expressive-authentication-oauth2#71](https://github.com/zendframework/zend-expressive-authentication-oauth2/pull/71) adds a check to `AccessTokenRepository` to verify that a row was returned before checking if a token was revoked, raising an exception if not.

- [zendframework/zend-expressive-authentication-oauth2#72](https://github.com/zendframework/zend-expressive-authentication-oauth2/pull/72) updates the database schema in provided examples to reflect actual requirements.

## 1.2.0 - 2019-09-01

### Added

- [zendframework/zend-expressive-authentication-oauth2#63](https://github.com/zendframework/zend-expressive-authentication-oauth2/pull/63)
  registers the `ConfigProvider` with the package. If you are using laminas-component-installer
  it will be added to your configuration during the installation.

- [zendframework/zend-expressive-authentication-oauth2#64](https://github.com/zendframework/zend-expressive-authentication-oauth2/pull/64)
  adds support for PHP 7.3.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 1.1.0 - 2018-11-19

### Added

- Nothing.

### Changed

- [zendframework/zend-expressive-authentication-oauth2#58](https://github.com/zendframework/zend-expressive-authentication-oauth2/pull/58)
  Upgrades the `league/oauth2-server` library to 7.3.0 in order to use it with
  [Swoole](https://www.swoole.co.uk/). This is provided by `league/oauth2-server`
  thanks to [zendframework/zend-expressive-authentication-oauth2#960 AuthorizationServer stateless](https://github.com/thephpleague/oauth2-server/pull/960)

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 1.0.1 - 2018-10-31

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-expressive-authentication-oauth2#52](https://github.com/zendframework/zend-expressive-authentication-oauth2/issues/52)
  Wrong factory mapped to AuthorizationHandler
- [zendframework/zend-expressive-authentication-oauth2#54](https://github.com/zendframework/zend-expressive-authentication-oauth2/pull/54)
  Fixed "WWW-Authenticate" header value format

## 1.0.0 - 2018-10-04

### Added

- [zendframework/zend-expressive-authentication-oauth2#41](https://github.com/zendframework/zend-expressive-authentication-oauth2/pull/41) Allows
  existing PDO service to be used. This will allow us to reuse existing pdo
  services instead of opening up a second connection for oauth.
- [zendframework/zend-expressive-authentication-oauth2#42](https://github.com/zendframework/zend-expressive-authentication-oauth2/pull/42) Adds `TokenEndpointHandler`,
  `AuthorizationMiddleware` and `AuthorizationHandler` in the `Mezzio\Authentication\OAuth2` namespace
  to [implement an authorization server](https://docs.mezzio.dev/mezzio-authentication-oauth2/v1/authorization-server/).
- [zendframework/zend-expressive-authentication-oauth2#50](https://github.com/zendframework/zend-expressive-authentication-oauth2/pull/50) Adds
  all the OAuth2 identity data generated by [thephpleague/oauth2-server](https://github.com/thephpleague/oauth2-server)
  to `UserInterface` PSR-7 attribute. These values are `oauth_user_id`,
  `oauth_client_id`, `oauth_access_token_id`, `oauth_scopes`.

### Changed

- [zendframework/zend-expressive-authentication-oauth2#42](https://github.com/zendframework/zend-expressive-authentication-oauth2/pull/42) Splits
  `Mezzio\Authentication\OAuth2\OAuth2Middleware` into individual implementations that allow
  [OAuth RFC-6749](https://tools.ietf.org/html/rfc6749) compliant authorization server implementations.

### Deprecated

- Nothing.

### Removed

- [zendframework/zend-expressive-authentication-oauth2#42](https://github.com/zendframework/zend-expressive-authentication-oauth2/pull/42) Removes
  `Mezzio\Authentication\OAuth2\OAuth2Middleware`.

### Fixed

- [zendframework/zend-expressive-authentication-oauth2#44](https://github.com/zendframework/zend-expressive-authentication-oauth2/pull/44/) Fixes
  revocation of access token for PDO repository
- [zendframework/zend-expressive-authentication-oauth2#45](https://github.com/zendframework/zend-expressive-authentication-oauth2/pull/45) Fixes
  issue with empty scope being passed throwing exception.

## 0.4.3 - 2018-05-09

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Removes auto-requiring of the encryption key via the configuration unless the default file
  actually exists and is readable. As the configuration is processed in every request, this is necessary
  to prevent issues when the file does not exist (e.g., if the user has specified an alternate location).

## 0.4.2 - 2018-05-09

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Fixes an issue in the default configuration paths for public, private, and encryption keys,
  ensuring they will be based on the current working directory, and not the package directory.

## 0.4.1 - 2018-05-09

### Added

- [zendframework/zend-expressive-authentication-oauth2#30](https://github.com/zendframework/zend-expressive-authentication-oauth2/pull/30)
  adds the AuthenticationInterface to the config provider so OAuth2 works out of
  the box. Can always be overwritten in project configs.
- [zendframework/zend-expressive-authentication-oauth2#38](https://github.com/zendframework/zend-expressive-authentication-oauth2/pull/38)
  added the the `/oauth` route configuration in docs.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-expressive-authentication-oauth2#21](https://github.com/zendframework/zend-expressive-authentication-oauth2/pull/21)
  fixes unknown user will throw an exception. When a user tries to use a
  username that doesn't exist in the database an exception is thrown instead of
  an invalid_credentials error.
- [zendframework/zend-expressive-authentication-oauth2#22](https://github.com/zendframework/zend-expressive-authentication-oauth2/pull/22)
  fixes exception thrown when client secret is missing. When a client id is used
  that has no client_secret in the database an exception is thrown instead of an
  invalid_client error.
- [zendframework/zend-expressive-authentication-oauth2#23](https://github.com/zendframework/zend-expressive-authentication-oauth2/pull/23)
  updates the token insert statements to match schema located in data/oauth2.php
- [zendframework/zend-expressive-authentication-oauth2#37](https://github.com/zendframework/zend-expressive-authentication-oauth2/pull/37)
  fixes issue with script to generate keys writes to vendor dir


## 0.4.0 - 2018-03-15

### Added

- [zendframework/zend-expressive-authentication-oauth2#9](https://github.com/zendframework/zend-expressive-authentication-oauth2/pull/9)
  adds support for PSR-15.

- [zendframework/zend-expressive-authentication-oauth2#13](https://github.com/zendframework/zend-expressive-authentication-oauth2/pull/13)
  adds `Mezzio\Authentication\OAuth2\Entity\RevokableTrait`, which
  provides a way to flag whether or not a token has been revoked, and mimics
  traits from the upstream league/oauth2-server implementation.

- [zendframework/zend-expressive-authentication-oauth2#13](https://github.com/zendframework/zend-expressive-authentication-oauth2/pull/13)
  adds `Mezzio\Authentication\OAuth2\Entity\TimestampableTrait`, which
  provides methods for setting and retrieving `DateTime` values representing
  creation and update timestamps for a token; it mimics traits from the upstream
  league/oauth2-server implementation.

- [zendframework/zend-expressive-authentication-oauth2#32](https://github.com/zendframework/zend-expressive-authentication-oauth2/pull/32)
  adds the ability to pull league/oauth2-server grant implementations from the
  container, providing factories for each grant type. It also adds the ability
  to selectively disable grant types via configuration.

### Changed

- Updates the repository to pin to mezzio-authentication `^0.4.0`.

- [zendframework/zend-expressive-authentication-oauth2#13](https://github.com/zendframework/zend-expressive-authentication-oauth2/pull/13)
  updates `Mezzio\Authentication\OAuth2\Entity\AccessTokenEntity` to
  use the `RevokableTrait` and `TimestampableTrait`.

- [zendframework/zend-expressive-authentication-oauth2#13](https://github.com/zendframework/zend-expressive-authentication-oauth2/pull/13)
  updates `Mezzio\Authentication\OAuth2\Entity\AuthCodeEntity` to
  use the `RevokableTrait`.

- [zendframework/zend-expressive-authentication-oauth2#13](https://github.com/zendframework/zend-expressive-authentication-oauth2/pull/13)
  updates `Mezzio\Authentication\OAuth2\Entity\RefreshTokenEntity` to
  use the `RevokableTrait`.

- [zendframework/zend-expressive-authentication-oauth2#13](https://github.com/zendframework/zend-expressive-authentication-oauth2/pull/13)
  updates `Mezzio\Authentication\OAuth2\Entity\ClientEntity` to
  use the `RevokableTrait` and `TimestampableTrait`. It also adds methods for
  setting and retrieving the client secret, personal access client, and password
  client.

- [zendframework/zend-expressive-authentication-oauth2#17](https://github.com/zendframework/zend-expressive-authentication-oauth2/pull/17)
  changes the constructor of each of the `Mezzio\Authentication\OAuth2\OAuth2Adapter`
  and `Mezzio\Authentication\OAuth2\OAuth2Middleware` classes to accept
  a callable `$responseFactory` instead of a `Psr\Http\Message\ResponseInterface`
  response prototype. The `$responseFactory` should produce a
  `ResponseInterface` implementation when invoked.

- [zendframework/zend-expressive-authentication-oauth2#17](https://github.com/zendframework/zend-expressive-authentication-oauth2/pull/17)
  updates the `OAuth2AdapterFactory` and `OAuth2MiddlewareFactory` classes to no
  longer use `Mezzio\Authentication\ResponsePrototypeTrait`, and
  instead always depend on the `Psr\Http\Message\ResponseInterface` service to
  correctly return a PHP callable capable of producing a `ResponseInterface`
  instance.

### Deprecated

- Nothing.

### Removed

- [zendframework/zend-expressive-authentication-oauth2#9](https://github.com/zendframework/zend-expressive-authentication-oauth2/pull/9) and
  [zendframework/zend-expressive-authentication-oauth2#5](https://github.com/zendframework/zend-expressive-authentication-oauth2/pull/5)
  remove support for http-interop/http-middleware and
  http-interop/http-server-middleware.

### Fixed

- [zendframework/zend-expressive-authentication-oauth2#18](https://github.com/zendframework/zend-expressive-authentication-oauth2/pull/18)
  updates the default SQL shipped with the package in `data/oauth2.sql` for
  generating OAuth2 tables to ensure it works with MySQL 5.7+; the SQL will
  still work with older versions, as well as other relational databases.

## 0.3.1 - 2018-02-28

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [zendframework/zend-expressive-authentication-oauth2#18](https://github.com/zendframework/zend-expressive-authentication-oauth2/pull/18)
  updates the default SQL shipped with the package in `data/oauth2.sql` for
  generating OAuth2 tables to ensure it works with MySQL 5.7+; the SQL will
  still work with older versions, as well as other relational databases.

## 0.3.0 - 2018-02-07

### Added

- [zendframework/zend-expressive-authentication-oauth2#11](https://github.com/zendframework/zend-expressive-authentication-oauth2/pull/11)
  adds support for mezzio-authentication 0.3.0.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 0.2.1 - 2017-12-11

### Added

- [zendframework/zend-expressive-authentication-oauth2#1](https://github.com/zendframework/zend-expressive-authentication-oauth2/pull/1)
  adds support for providing configuration for the cryptographic key. This may
  be done by providing any of the following via the `authentication.private_key`
  configuration:

  - A string representing the key.
  - An array with the following key/value pairs:
    - `key_or_path` representing either the key or a path on the filesystem to a key.
    - `pass_phrase` with the pass phrase to use with the key, if needed.
    - `key_permissions_check`, a boolean for indicating whether or not to verify
      permissions of the key file before attempting to load it.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 0.2.0 - 2017-11-28

### Added

- Adds support for mezzio-authentication 0.2.0.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Removes support for mezzio-authentication 0.1.0.

### Fixed

- Nothing.

## 0.1.0 - 2017-11-20

Initial release.

### Added

- Everything.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.
