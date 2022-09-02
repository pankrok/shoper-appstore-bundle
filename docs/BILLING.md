#Creating Billing addres

After registration in ShoperAppstore new aplication you have to add application URL for automatic messages \
more info here [developers.shoper.pl](https://developers.shoper.pl/developers/appstore/billing-system)

AppstoreBundle can create Billing Controller from console:

```bash
 php bin/console make:shoper-billing-controller
```

makre will create a controller that will handle all requests sent by Shoper to the application (installation, subscription, update, deletion)