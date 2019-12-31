# Usage

If you successfully configured the OAuth2 server as detailed in the
[installation](intro.md) section, you can request an access token using the
OAuth2 server route defined [before](intro.md#configure-the-oauth2-route)
(e.g. `/oauth`).

You can require an access token using one of the following scenarios:

- [client credentials](grant/client_credentials.md);
- [password](grant/password.md);
- [authorization code](grant/auth_code.md);
- [implicit](grant/implicit.md);
- [refresh token](grant/refresh_token.md).

## Authenticate a middleware

This library uses the authentication abstraction of the `Mezzio\Authentication\AuthenticationMiddleware`
class provided by [mezzio-authentication](https://github.com/mezzio/mezzio-authentication).

In order to use OAuth2 we need to configure the service
`Mezzio\Authentication\AuthenticationInterface` to resolve in
`Mezzio\Authentication\OAuth2\OAuth2Adapter`. Using the
[laminas-servicemanager](https://github.com/laminas/laminas-servicemanager) this
can be achieved using `aliases` with the following configuration:

```php
use Mezzio\Authentication;

return [
    'dependencies' => [
        'aliases' => [
            Authentication\AuthenticationInterface::class => Authentication\OAuth2\OAuth2Adapter::class,
        ],
    ],
];
```

The previous configuration will instruct `mezzio-authentication` to use
the OAuth2 adapter. This adapter does not require a `Mezzio\Authentication\UserRepositoryInterface`.
The OAuth2 database with user and client credentials is managed by the component
itself.

When the service alias is configured, you can immediately begin authenticating
your application/API by adding the `AuthenticationMiddleware` to either your
application or route-specific middleware pipeline. For instance, using an
[Mezzio](https://docs.mezzio.dev/mezzio/) application, you
could add it to a specific route, as follows:

```php
$app->post('/api/users', [
    Mezzio\Authentication\AuthenticationMiddleware::class,
    App\Action\AddUserAction::class,
], 'api.add.user');
```
