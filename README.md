# OAuth2 server middleware for Mezzio and PSR-7 applications

[![Build Status](https://github.com/mezzio/mezzio-authentication-oauth2/workflows/Continuous%20Integration/badge.svg)](https://github.com/mezzio/mezzio-authentication-oauth2/actions?query=workflow%3A"Continuous+Integration")

Laminas-mezzio-authentication-oauth2 is middleware for [Mezzio](https://github.com/mezzio/mezzio)
and [PSR-7](http://www.php-fig.org/psr/psr-7/) applications providing an OAuth2
server for authentication.

This library uses the [league/oauth2-server](https://oauth2.thephpleague.com/)
package for implementing the OAuth2 server. It supports all the following grant
types:

- client credentials;
- password;
- authorization code;
- implicit;
- refresh token;

## Installation

You can install the *mezzio-authentication-oauth2* library with
composer:

```bash
$ composer require mezzio/mezzio-authentication-oauth2
```

## Documentation

Browse the documentation online at https://docs.mezzio.dev/mezzio-authentication-oauth2/

## Support

* [Issues](https://github.com/mezzio/mezzio-authentication-oauth2/issues/)
* [Chat](https://laminas.dev/chat/)
* [Forum](https://discourse.laminas.dev/)
