# Unofficial Shoper Appstore Symfony 6 Bundle
## General info
This bundle contains everything necessary to code an application for Shoper. It gives the possibility to use both the OAuth system as well as the user / password. It is not official code provided by the Shoper software developers.
Current version:
> 1.1.0

## Table of contents
* [Technologies](#technologies)
* [Setup](#setup)
* [Examples](#examples)
* [Status](#status)

## Technologies
* PHP 8.2
* Symfony 6.4
* Doctrine
* Twig
* HTTPClient
* Symfony MakerBundle

## Setup
* Install Symfony 6.4 LTS
* Add pankrok/shoper-appstore-bundle location your `composer.json` and run composer

``` bash
composer require pankrok/shoper-appstore-bundle "^1.1.0"
```

* create appstore.yaml in config/packages and fill in the data:
```yaml
shoper_appstore:
    appId: appId    
    appSecret: appSecret
    appstoreSecret: appstoreSecret
```
create database tabels:
``` bash
php bin/console make:migration
php bin/console doctrine:migrations:migrate
```

token refresh
``` bash
php bin/console ShoperAppstoreBundle:TokenRefresh
```

## Examples

* [ApiController](docs/APICONTROLLER.md) 
* [Aurora forms](docs/AURORAFORMS.md)
* [Auth](docs/AUTH.md) 
* [Billing system](docs/BILLING.md) 
* [Events](docs/EVENTS.md)
* [Iframe init](docs/IFRAME.md)
* [Resources](docs/RESOURCES.md)
* [ShoperController](docs/SHOPERCONTROLLER.md)
* [Twig filters](docs/TWIGFILTERS.md)
* [Webhook](docs/WEBHOOK.md)

## Status
> Project is: _in progress_ 
