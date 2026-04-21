# OAuth2 server middleware for Mezzio and PSR-7 applications

[![Build Status](https://github.com/mezzio/mezzio-authentication-oauth2/workflows/Continuous%20Integration/badge.svg)](https://github.com/mezzio/mezzio-authentication-oauth2/actions?query=workflow%3A"Continuous+Integration")

> ## 🇷🇺 Русским гражданам
>
> Мы, участники Laminas, родились и живем в разных странах. У многих из нас есть друзья, родственники и коллеги как в России, так и в Украине. Некоторые из нас родились в России. Некоторые из нас живут в России. У некоторых бабушки и дедушки сражались с фашистами во Второй мировой войне. Здесь никто не поддерживает фашизм.
>
> У одного из нас есть украинская родственница, которая спаслась из дома вместе с сыном. Поезд задержался из-за бомбежки на дороге впереди. У нас есть друзья, которые прячутся в бомбоубежищах. Мы с тревогой ждем весточки от них после воздушных налетов, которые беспорядочно наносят удары и попадают по больницам, школам, детским садам и домам. Мы не берем это из каких-либо СМИ. Мы наблюдаем это напрямую.
>
> Вы доверяете нам достаточно, чтоб использовать наши программы, и мы просим вас довериться нам вновь. Мы нуждаемся в помощи. Выходите и протестуйте против этой бесполезной войны. Остановите кровопролитие. Скажите "Нет войне!"
>
> ## 🇺🇸 To Citizens of Russia
>
> We at Laminas come from all over the world. Many of us have friends, family and colleagues in both Russia and Ukraine. Some of us were born in Russia. Some of us currently live in Russia. Some have grandparents who fought Nazis in World War II. Nobody here supports fascism.
>
> One team member has a Ukrainian relative who fled her home with her son. The train was delayed due to bombing on the road ahead. We have friends who are hiding in bomb shelters. We anxiously follow up on them after the air raids, which indiscriminately fire at hospitals, schools, kindergartens and houses. We're not taking this from any media. These are our actual experiences.
>
> You trust us enough to use our software. We ask that you trust us to say the truth on this. We need your help. Go out and protest this unnecessary war. Stop the bloodshed. Say "stop the war!"

Laminas-mezzio-authentication-oauth2 is middleware for [Mezzio](https://github.com/mezzio/mezzio)
and [PSR-7](https://www.php-fig.org/psr/psr-7/) applications providing an OAuth2
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

- [Issues](https://github.com/mezzio/mezzio-authentication-oauth2/issues/)
- [Chat](https://laminas.dev/chat/)
- [Forum](https://discourse.laminas.dev/)
