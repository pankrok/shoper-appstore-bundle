# Shoper Controller

## Generating the controller

```bash
php bin/console make:shoper-controller
```

The maker generates a controller pre-wired with `ApiController` injection and `ShoperApiException` error handling.

## GET — fetch a resource list

```php
namespace App\Controller;

use PanKrok\ShoperAppstoreBundle\Controller\ApiController;
use PanKrok\ShoperAppstoreBundle\Exception\ShoperApiException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class IndexController extends AbstractController
{
    #[Route('/', name: 'index')]
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
}
```

## GET — fetch a single object by ID

```php
$product = $api->product->get(42)->getBodyArray();
```

## POST — insert object

> Upon success returns the ID of the created object.

```php
$data = [
    'category_id' => 1,
    'producer_id' => 1,
    'translations' => [
        'pl_PL' => [
            'name'        => 'Product name',
            'description' => 'Product description',
            'active'      => true,
        ],
    ],
    'stock' => [
        'price'  => 10,
        'active' => 1,
        'stock'  => 10,
    ],
    'tax_id'  => 1,
    'code'    => '1234567',
    'unit_id' => 1,
];

$id = $api->product->post($data);
```

## PUT — update object

> Upon success returns `true`.

```php
$productId = 42;
$data = [
    'stock' => [
        'price' => 19.99,
    ],
];

$result = $api->product->put($productId, $data);
```

## DELETE — delete object

> Upon success no response body is returned.

```php
$api->product->delete(42);
```

## Filtering and pagination

`setLimit()` accepts values between 1 and 50 (`ResourceModel::MAX_LIMIT`).

```php
$products = $api->product
    ->setFilters(['stock.price' => ['gt' => 10]])
    ->setOrder('add_date desc')
    ->setLimit(50)
    ->setPage(2)
    ->get()
    ->getBodyArray();
```

## Bulk requests

Send multiple requests in one HTTP call. Bulk supports all CRUD methods.

```php
$results = $api->bulk
    ->product
        ->setLimit(10)
        ->setPage(1)
        ->get()
    ->product
        ->setLimit(10)
        ->setPage(2)
        ->get()
    ->send()
    ->getBodyArray();
```
