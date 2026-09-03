# ApiController

`ApiController` is the central service for communicating with the Shoper REST API. Inject it into your Symfony controllers or services via the DI container.

## Method reference

### initFromRequest(array $params, bool $checkHash = true): void

Initialises the shop context from Shoper iframe query parameters (`shop`, `place`, `timestamp`, `hash`). Verifies the HMAC hash by default. Automatically refreshes the OAuth token if it expires within 24 hours.

```php
$api->initFromRequest($request->query->all());
```

---

### refreshToken(Shops $shop): void

Refreshes the OAuth token for the given shop entity. Used primarily in CRON commands.

```php
$api->refreshToken($shop);
```

---

### useBasicAuth(?string $url = null, array $basicAuth = []): BearerInterface

Switches to HTTP Basic Auth (admin username / password) mode. Pass `$url` and `$basicAuth` to override YAML config values.

```php
$client = $api->useBasicAuth('https://myshop.pl', [
    'login'    => 'admin',
    'password' => 'secret',
]);
```

---

### getRequestParams(): array

Returns the query parameters that were passed to `initFromRequest()`.

```php
$params = $api->getRequestParams();
// $params['shop'], $params['place'], $params['timestamp']
```

---

### setHttpClient(BearerInterface $client): void / getHttpClient(): BearerInterface

Inject or retrieve the underlying HTTP client (useful for testing).

---

### setShopUrl(string $url): void / getShopUrl(): ?string

Set or get the active shop URL string.

---

### bindShop(Shops $shop): void / getActiveShop(): ?Shops

Bind a `Shops` entity as the current context, or retrieve the active one.

```php
$api->bindShop($shop);
$shop = $api->getActiveShop();
```

---

### isOAuthMode(): bool

Returns `true` when the bundle is configured in OAuth (Appstore) mode (i.e. `appId` is set in config).

```php
if ($api->isOAuthMode()) { ... }
```

---

## Deprecated methods

The following method names were used in versions prior to 1.2.0. They are kept as aliases and will trigger `E_USER_DEPRECATED`. Migrate to the new names listed above.

| Deprecated (≤ 1.1.x)       | Replacement (≥ 1.2.0)    |
|-----------------------------|--------------------------|
| `setParams()`               | `initFromRequest()`      |
| `refreshShopToken()`        | `refreshToken()`         |
| `setBasicAuth()`            | `useBasicAuth()`         |
| `getParams()`               | `getRequestParams()`     |
| `setClient()`               | `setHttpClient()`        |
| `getClient()`               | `getHttpClient()`        |
| `setShop()`                 | `bindShop()`             |
| `getShop()`                 | `getActiveShop()`        |
| `getAppId()`                | `isOAuthMode()`          |

---

## Resource access

Resources are accessed as magic properties of `ApiController`. See [RESOURCES.md](RESOURCES.md) for the full list.

```php
$products = $api->product->get()->getBodyArray();
$api->product->put(42, ['stock' => ['price' => 19.99]]);
```
