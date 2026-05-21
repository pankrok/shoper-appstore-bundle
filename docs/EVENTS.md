# Events

AppstoreBundle dispatches events at each stage of the Shoper application lifecycle. Pre-events fire before the database operation; post-events fire after.

## Event reference

| Event constant | Event name | Trigger |
|---|---|---|
| `PreBillingInstallEvent::NAME` | `appstore.pre_billing_install` | Before processing an installation payment |
| `BillingInstallEvent::NAME` | `appstore.billing_install` | [Application installation payment](https://developers.shoper.pl/developers/appstore/billing-system/automatic-messages#installation-paid) received |
| `PreBillingSubscriptionEvent::NAME` | `appstore.pre_billing_subscription` | Before processing a subscription payment |
| `BillingSubscriptionEvent::NAME` | `appstore.billing_subscription` | [Subscription payment](https://developers.shoper.pl/developers/appstore/billing-system/automatic-messages#subscription-paid) received |
| `PreInstallEvent::NAME` | `appstore.pre_install` | Before saving a new shop installation to the database |
| `InstallEvent::NAME` | `appstore.install` | [Application installed](https://developers.shoper.pl/developers/appstore/billing-system/automatic-messages#install) — shop and token saved |
| `PostInstallEvent::NAME` | `appstore.post_install` | After successful installation (token persisted) |
| `PostBillingInstallEvent::NAME` | `appstore.post_billing_install` | After billing install event is processed |
| `PreUpgradeEvent::NAME` | `appstore.pre_upgrade` | Before updating shop version in the database |
| `UpgradeEvent::NAME` | `appstore.upgrade` | [Application upgraded](https://developers.shoper.pl/developers/appstore/billing-system/automatic-messages#upgrade) — shop version and URL updated |
| `PreUninstallEvent::NAME` | `appstore.pre_uninstall` | Before removing a shop from the database |
| `UninstallEvent::NAME` | `appstore.uninstall` | [Application uninstalled](https://developers.shoper.pl/developers/appstore/billing-system/automatic-messages#uninstall) — shop and token removed |

## Usage

### Attribute-based listener (recommended)

```php
use PanKrok\ShoperAppstoreBundle\Events\InstallEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: InstallEvent::NAME)]
class AppInstallListener
{
    public function __invoke(InstallEvent $event): void
    {
        $payload = $event->getPayload();
        // $payload['shop']                — shop domain
        // $payload['shop_url']            — full shop URL
        // $payload['application_version'] — installed version
    }
}
```

### EventSubscriber

```php
use PanKrok\ShoperAppstoreBundle\Events\InstallEvent;
use PanKrok\ShoperAppstoreBundle\Events\UninstallEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AppLifecycleSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            InstallEvent::NAME   => 'onInstall',
            UninstallEvent::NAME => 'onUninstall',
        ];
    }

    public function onInstall(InstallEvent $event): void
    {
        $payload = $event->getPayload();
        // provision tenant, send welcome email, etc.
    }

    public function onUninstall(UninstallEvent $event): void
    {
        $payload = $event->getPayload();
        // clean up tenant data, etc.
    }
}
```
