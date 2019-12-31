# OAuth2 server middleware for Mezzio and PSR-7 applications

[![Build Status](https://travis-ci.org/mezzio/mezzio-authentication-oauth2.svg?branch=master)](https://travis-ci.org/mezzio/mezzio-authentication-oauth2)
[![Coverage Status](https://coveralls.io/repos/github/mezzio/mezzio-authentication-oauth2/badge.svg?branch=master)](https://coveralls.io/github/mezzio/mezzio-authentication-oauth2?branch=master)

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

## WORK IN PROGRESS

This repository contains a **work in progress** project for building an
authentication module for Mezzio and PSR-7 applications.
It also provides an OAuth2 adapter for [mezzio-authentication](https://github.com/mezzio/mezzio-authentication)
library.


**Please, don't use this code in a production environment!**

## Installation

You can install the *mezzio-authentication-oauth2* library with
composer:

```bash
$ composer require mezzio/mezzio-authentication-oauth2
```

## Documentation

Documentation is [in the doc tree](doc/book/), and can be compiled using [mkdocs](https://www.mkdocs.org):

```bash
$ mkdocs build
```

You may also [browse the documentation online](https://docs.mezzio.dev/mezzio-authentication-oauth2/).
