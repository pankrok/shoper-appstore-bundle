# Authentication

AppstoreBundle supports two authentication modes.

## OAuth mode (Appstore)

Configure `config/packages/appstore.yaml`:

```yaml
shoper_appstore:
    appId: appId
    appSecret: appSecret
    appstoreSecret: appstoreSecret
```

In OAuth mode the bundle handles token acquisition and refresh automatically. Inject `ApiController` and use resources directly:

```php
use PanKrok\ShoperAppstoreBundle\Controller\ApiController;
use PanKrok\ShoperAppstoreBundle\Exception\ShoperApiException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/index', name: 'index')]
public function index(ApiController $api): Response
{
    try {
        $data = $api->product->get()->getBodyArray();
    } catch (ShoperApiException $e) {
        throw $e;
    }

    return $this->render('index/index.html.twig', [
        'data' => $data,
    ]);
}
```

## Basic Auth mode (admin username/password)

Configure `config/packages/appstore.yaml`:

```yaml
shoper_appstore:
    username: adminUsername
    password: adminPassword
    shopurl: https://yourshop.com
```

Then call `useBasicAuth()` followed by `auth()` to obtain a token:

```php
#[Route('/index', name: 'index')]
public function index(ApiController $api): Response
{
    $api->useBasicAuth()->auth();

    $data  = $api->product->get()->getBodyArray();
    $token = $api->getHttpClient()->getToken();

    return $this->render('index/index.html.twig', [
        'data' => $data,
    ]);
}
```

`useBasicAuth()` also accepts runtime credentials, bypassing the YAML config entirely:

```php
$api->useBasicAuth('https://yourshop.com', [
    'username' => 'admin',
    'password' => 'secret',
])->auth();

$data = $api->product->get()->getBodyArray();
```

## Deprecated method names

The following names were renamed in 1.2.0. Old names still work but emit `E_USER_DEPRECATED`.

| Deprecated (≤ 1.1.x) | Replacement (≥ 1.2.0) |
|---|---|
| `setBasicAuth()` | `useBasicAuth()` |
| `getClient()` | `getHttpClient()` |
| `setClient()` | `setHttpClient()` |
