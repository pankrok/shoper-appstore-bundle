# Webhooks

Shoper can send webhook notifications to your application. AppstoreBundle provides `WebhookController` to verify the request checksum and set up the API client for the calling shop.

## Generating the controller

```bash
php bin/console make:shoper-webhook-controller
```

The maker asks for a controller name and an optional webhook secret. The secret can also be configured later directly in the generated file.

## How it works

`WebhookController::checksum()` verifies the `X-Webhook-SHA1` header against the request body and the shop's license key. On success it initialises `ApiController` for that shop — ready to use via `getApiClient()`.

## Example

```php
namespace App\Controller;

use PanKrok\ShoperAppstoreBundle\Controller\WebhookController as Webhook;
use PanKrok\ShoperAppstoreBundle\Exception\ShoperApiException;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class OrderWebhookController extends AbstractController
{
    #[Route('/webhook/order', name: 'webhook_order')]
    public function index(Request $request, Webhook $webhook, LoggerInterface $logger): Response
    {
        try {
            $webhook->checksum($request, 'your-webhook-secret');
            $api = $webhook->getApiClient();

            // Your webhook logic here.
            // Example: fetch the order that triggered the event.
            // $data = json_decode($request->getContent(), true);
            // $order = $api->order->get($data['object_id'])->getBodyArray();

            return new Response('', Response::HTTP_OK);
        } catch (ShoperApiException $e) {
            $logger->error('Webhook API error: ' . $e->getMessage(), [
                'error' => $e->getShoperError(),
            ]);

            return new Response('', Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Exception $e) {
            $logger->error('Webhook error: ' . $e->getMessage());

            return new Response('', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
```

## Webhooks created directly in the store

When a webhook is created in the shop admin panel (not via the Appstore), pass `false` as the third argument to skip the `X-Shop-License` header check:

```php
$webhook->checksum($request, 'your-secret', false);
$api = $webhook->getApiClient();
```

## Obtaining the API client

```php
$api = $webhook->getApiClient();

// Use any resource
$products = $api->product->get()->getBodyArray();
```
