# Unofficial Shoper Appstore Symfony 5 Bundle
## General info
This bundle contains everything necessary to code an application for Shoper. It gives the possibility to use both the OAuth system as well as the user / password. It is not official code provided by the Shoper software developers.
Current version:
> 1.0.0

## Table of contents
* [Technologies](#technologies)
* [Setup](#setup)
* [Examples](#examples)
* [Status](#status)

## Technologies
* PHP 8
* Symfony 5
* Doctrine
* Twig
* HTTPClient
* Symfony MakerBundle

## Setup
* Install Symfony 5
* Add pankrok/shoper-appstore-bundle location your `composer.json` and run composer

``` bash
composer require pankrok/shoper-appstore-bundle "^1.0.0"
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

## Examples

* [Auth](docs/AUTH.md) 
* [Billing system](docs/BILLING.md) 
* [ShoperController](docs/SHOPERCONTROLLER.md)
* [Webhook](docs/WEBHOOK.md)
* [iframe Init](docs/IFRAME.md)
* [Aurora forms](docs/AURORAFORMS.md)
* [Twig filters](docs/TWIGFILTERS.md)
* [Resources](docs/RESOURCES.md)

## Status
> Project is: _in progress_ 