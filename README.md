# Shoper Appstore Symfony Bundle

Unofficial Symfony 6.4 bundle for building applications on the [Shoper Appstore](https://developers.shoper.pl/developers/appstore). Provides OAuth integration, REST API client, billing and webhook handling, Twig helpers, and Maker commands for scaffolding controllers. Not official Shoper software.

Current version: **1.2.0**

## Table of contents

- [Technologies](#technologies)
- [Setup](#setup)
- [Configuration](#configuration)
- [Token refresh CRON](#token-refresh-cron)
- [Error handling](#error-handling)
- [Documentation](#documentation)

## Technologies

- PHP 8.2
- Symfony 6.4 LTS
- Doctrine ORM
- Twig
- Symfony HttpClient
- Symfony MakerBundle

## Setup

Install via Composer:

```bash
composer require pankrok/shoper-appstore-bundle "^1.2.0"
```

Create the database tables:

```bash
php bin/console make:migration
php bin/console doctrine:migrations:migrate
```

## Configuration

### OAuth mode (Appstore)

Create `config/packages/appstore.yaml`:

```yaml
shoper_appstore:
    appId: your_app_id
    appSecret: your_app_secret
    appstoreSecret: your_appstore_secret
    jssdk: https://dcsaascdn.net/js/dc-sdk-1.0.5.min.js
```

### Basic Auth mode (admin username/password)

```yaml
shoper_appstore:
    username: admin_username
    password: admin_password
    shopurl: https://yourshop.com
    jssdk: https://dcsaascdn.net/js/dc-sdk-1.0.5.min.js
```

## Token refresh CRON

Run the token refresh command every 4 hours to keep OAuth tokens valid:

```bash
php bin/console shoper:token:refresh
```

Available options:

| Option | Default | Description |
|---|---|---|
| `--limit` | `200` | Maximum number of tokens to refresh per run |
| `--hours-ahead` | `23` | Refresh tokens expiring within this many hours (max 23) |

Example crontab entry (every 4 hours):

```
0 */4 * * * cd /var/www/html && php bin/console shoper:token:refresh --limit=200 --hours-ahead=23
```

> **Note:** The old command name `ShoperAppstoreBundle:TokenRefresh` is kept as a BC alias.

## Error handling

All Shoper API errors throw `ShoperApiException`, which extends Symfony's `HttpException` and carries the original error code and description from the API response.

The bundle ships an `ExceptionSubscriber` that automatically intercepts `ShoperApiException` and renders a clean Aurora-compatible error page instead of the Symfony debug page — no configuration needed.

To override the error template in your application, create:

```
templates/bundles/Appstore/error.html.twig
```

Catching the exception manually in a controller:

```php
use PanKrok\ShoperAppstoreBundle\Exception\ShoperApiException;

try {
    $data = $api->product->get()->getBodyArray();
} catch (ShoperApiException $e) {
    // $e->getShoperError()            → 'invalid_token'
    // $e->getShoperErrorDescription() → 'Token has expired'
    // $e->getStatusCode()             → 401
    throw $e;
}
```

## Documentation

| Topic | File |
|---|---|
| ApiController reference | [docs/APICONTROLLER.md](docs/APICONTROLLER.md) |
| OAuth & Basic Auth | [docs/AUTH.md](docs/AUTH.md) |
| Aurora forms | [docs/AURORAFORMS.md](docs/AURORAFORMS.md) |
| Billing system | [docs/BILLING.md](docs/BILLING.md) |
| Events | [docs/EVENTS.md](docs/EVENTS.md) |
| Iframe / JS SDK | [docs/IFRAME.md](docs/IFRAME.md) |
| API Resources | [docs/RESOURCES.md](docs/RESOURCES.md) |
| Shoper Controller | [docs/SHOPERCONTROLLER.md](docs/SHOPERCONTROLLER.md) |
| Twig filters | [docs/TWIGFILTERS.md](docs/TWIGFILTERS.md) |
| Webhooks | [docs/WEBHOOK.md](docs/WEBHOOK.md) |
