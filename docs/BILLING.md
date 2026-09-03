# Billing system

After registering a new application in Shoper Appstore you must configure an **Application URL** to receive automatic messages (billing events).

More info: [developers.shoper.pl — Billing system](https://developers.shoper.pl/developers/appstore/billing-system)

## Generating the controller

```bash
php bin/console make:shoper-billing-controller
```

The maker generates a controller pre-wired to handle all five Shoper automatic messages:

| Shoper action | Event fired |
|---|---|
| Application installation payment | `appstore.billing_install` |
| Subscription payment | `appstore.billing_subscription` |
| Application is installed | `appstore.install` |
| Application is upgraded | `appstore.upgrade` |
| Application is uninstalled | `appstore.uninstall` |

## Generated controller

```php
namespace App\Controller;

use PanKrok\ShoperAppstoreBundle\Controller\AppstoreBillingController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class BillingController extends AbstractController
{
    #[Route('/billing', name: 'billing_billing')]
    public function index(Request $request, AppstoreBillingController $billing): Response
    {
        return $billing->init($request->request->all());
    }
}
```

> The route name **must** start with `billing_` — the bundle's `RequestSubscriber` skips HMAC verification for billing routes to allow Shoper's server-to-server calls through.

## Reacting to billing events

Subscribe to any billing event in your application:

```php
use PanKrok\ShoperAppstoreBundle\Events\BillingInstallEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: BillingInstallEvent::NAME)]
class BillingInstallListener
{
    public function __invoke(BillingInstallEvent $event): void
    {
        $payload = $event->getPayload();
        // $payload['shop'], $payload['action'], ...
    }
}
```

See [EVENTS.md](EVENTS.md) for the full list of available events.
