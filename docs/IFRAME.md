# Iframe integration

Shoper Appstore applications are displayed inside the store's administration panel via an iframe. AppstoreBundle provides automatic JS SDK loading and Aurora styles.

More info: [developers.shoper.pl — JS SDK](https://developers.shoper.pl/developers/appstore/shop-integration/js-sdk)

## JS SDK

The SDK URL is configured in `config/packages/appstore.yaml`:

```yaml
shoper_appstore:
    jssdk: https://dcsaascdn.net/js/dc-sdk-1.0.5.min.js
```

The bundle registers the URL as a Twig global variable `shoper_js_sdk` and includes it automatically on every request via `JsSdkSubscriber`.

To include the SDK and iframe init snippet in your base Twig template:

```twig
{% include '@Appstore/js_sdk.html.twig' %}
```

Place this inside the `<head>` or at the end of `<body>` in your layout file.

## Context data (iframe params)

When your app is opened in the iframe, Shoper passes context via query parameters (`shop`, `place`, `timestamp`, `hash`). The bundle reads and verifies these automatically in `RequestSubscriber` on every request.

You can access them in a controller via `ApiController`:

```php
public function index(ApiController $api): Response
{
    $params = $api->getRequestParams();
    // $params['shop'], $params['place'], $params['timestamp']
}
```
