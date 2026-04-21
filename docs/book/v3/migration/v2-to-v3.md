# Migration from Version 2 to 3

## Changed Behaviour & Signatures

### AuthorizationMiddleware

Previously, any exception not of type `League\OAuth2\Server\Exception\OAuthServerException` thrown during the authorization request validation process in `Mezzio\Authentication\OAuth2\AuthorizationMiddleware` would result in an `OAuthServerException` containing the original exception message.

In version 3, such exceptions are now caught, logged (if a logger is provided), and a generic "An internal error occurred" message is returned instead.
**This prevents potential leakage of sensitive information in exception messages.**

### league/oauth2-server Upgrade

Version 3 of `mezzio-authentication-oauth2` upgrades `league/oauth2-server` from version 8 to version 9.  
Check the [league/oauth2-server changelog](https://github.com/thephpleague/oauth2-server/blob/master/CHANGELOG.md) for details.
