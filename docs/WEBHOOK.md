# Creating Shoper webhook contoller

Shoper allows you to create webhooks, AppstoreBundle allows you to create a controller that will receive the webhook and check its checksum.
```bash
 php bin/console make:shoper-webhook-controller
```

the following code shows a simple use of the order.create webhook
```php
<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Pankrok\ShoperAppstoreBundle\Controller\WebhookController as Webhook;
use Psr\Log\LoggerInterface;

class WebhookController extends AbstractController
{
    #[Route('/webhook', name: 'webhook')]
    public function index(Request $request, Webhook $webhook, LoggerInterface $logger): Response
    {
        try {
            $webhook = $webhook->checksum($request, '12345')->getWebhookData();
            // do stuff
            
            // get API client
            $api = $webhook->getApiClient();
            
            return new Response(200);
        } catch (\Exception $e) {
            $logger->error('Webhook Error: ' . $e->getMessage());
            
            return new Response(500);
        }
    }
}
```

You can obtain API Clinet from webhook service by method getApiClient():
```php
$api = $webhook->getApiClient();
```

If the webhook is generated directly in the store, false param should passed as in the example below:
```php
$webhook->checksum($request, '12345', false)->getWebhookData();
```