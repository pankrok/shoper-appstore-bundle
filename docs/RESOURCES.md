# Resources

All resources are accessed via `ApiController` using camelCase property names. Each resource supports the standard CRUD methods: `get()`, `post()`, `put()`, `delete()`.

## Usage

```php
// GET list
$products = $api->product->get()->getBodyArray();

// GET single by ID
$product = $api->product->get(42)->getBodyArray();

// POST (create)
$id = $api->product->post(['translations' => [...], 'stock' => [...]]);

// PUT (update)
$api->product->put(42, ['stock' => ['price' => 19.99]]);

// DELETE
$api->product->delete(42);
```

## Available resources

| Property name | Shoper API docs |
|---|---|
| `aboutPage` | [aboutpages](https://developers.shoper.pl/developers/api/resources/aboutpages) |
| `additionalField` | [additional-fields](https://developers.shoper.pl/developers/api/resources/additional-fields) |
| `applicationConfig` | [application-config](https://developers.shoper.pl/developers/api/resources/application-config) |
| `applicationLock` | [application-lock](https://developers.shoper.pl/developers/api/resources/application-lock) |
| `applicationVersion` | [application-version](https://developers.shoper.pl/developers/api/resources/application-version) |
| `attribute` | [attributes](https://developers.shoper.pl/developers/api/resources/attributes) |
| `attributeGroup` | [attribute-groups](https://developers.shoper.pl/developers/api/resources/attribute-groups) |
| `auction` | [auctions](https://developers.shoper.pl/developers/api/resources/auctions) |
| `auctionHouse` | [auction-houses](https://developers.shoper.pl/developers/api/resources/auction-houses) |
| `auctionOrder` | [auction-orders](https://developers.shoper.pl/developers/api/resources/auction-orders) |
| `availability` | [availabilitys](https://developers.shoper.pl/developers/api/resources/availabilitys) |
| `categoriesTree` | [categories-tree](https://developers.shoper.pl/developers/api/resources/categories-tree) |
| `category` | [categories](https://developers.shoper.pl/developers/api/resources/categorys) |
| `collection` | [collections](https://developers.shoper.pl/developers/api/resources/collections) |
| `currency` | [currencies](https://developers.shoper.pl/developers/api/resources/currencys) |
| `dashboardActivity` | [dashboard-activity](https://developers.shoper.pl/developers/api/resources/dashboard-activitys) |
| `dashboardStat` | [dashboard-stats](https://developers.shoper.pl/developers/api/resources/dashboard-stats) |
| `delivery` | [deliveries](https://developers.shoper.pl/developers/api/resources/deliveries) |
| `gauge` | [gauges](https://developers.shoper.pl/developers/api/resources/gauges) |
| `geolocationCountry` | [geolocation-countries](https://developers.shoper.pl/developers/api/resources/geolocation-countrys) |
| `geolocationRegion` | [geolocation-regions](https://developers.shoper.pl/developers/api/resources/geolocation-regions) |
| `geolocationSubregion` | [geolocation-subregions](https://developers.shoper.pl/developers/api/resources/geolocation-subregions) |
| `language` | [languages](https://developers.shoper.pl/developers/api/resources/languages) |
| `loyaltyEvent` | [loyalty-events](https://developers.shoper.pl/developers/api/resources/loyalty-events) |
| `metafield` | [metafields](https://developers.shoper.pl/developers/api/resources/metafields) |
| `metafieldValue` | [metafield-values](https://developers.shoper.pl/developers/api/resources/metafield-values) |
| `news` | [news](https://developers.shoper.pl/developers/api/resources/news) |
| `newsCategory` | [news-categories](https://developers.shoper.pl/developers/api/resources/news-categorys) |
| `newsComment` | [news-comments](https://developers.shoper.pl/developers/api/resources/news-comments) |
| `newsFile` | [news-files](https://developers.shoper.pl/developers/api/resources/news-files) |
| `newsTag` | [news-tags](https://developers.shoper.pl/developers/api/resources/news-tags) |
| `objectMtime` | [object-mtime](https://developers.shoper.pl/developers/api/resources/object-mtime) |
| `option` | [options](https://developers.shoper.pl/developers/api/resources/options) |
| `optionGroup` | [option-groups](https://developers.shoper.pl/developers/api/resources/option-groups) |
| `optionValue` | [option-values](https://developers.shoper.pl/developers/api/resources/option-values) |
| `order` | [orders](https://developers.shoper.pl/developers/api/resources/orders) |
| `orderProduct` | [order-products](https://developers.shoper.pl/developers/api/resources/order-products) |
| `orderTag` | [order-tags](https://developers.shoper.pl/developers/api/resources/order-tags) |
| `parcel` | [parcels](https://developers.shoper.pl/developers/api/resources/parcels) |
| `payment` | [payments](https://developers.shoper.pl/developers/api/resources/payments) |
| `producer` | [producers](https://developers.shoper.pl/developers/api/resources/producers) |
| `product` | [products](https://developers.shoper.pl/developers/api/resources/products) |
| `productFile` | [product-files](https://developers.shoper.pl/developers/api/resources/product-files) |
| `productImage` | [product-images](https://developers.shoper.pl/developers/api/resources/product-images) |
| `productStock` | [product-stocks](https://developers.shoper.pl/developers/api/resources/product-stocks) |
| `productTag` | [product-tags](https://developers.shoper.pl/developers/api/resources/product-tags) |
| `progress` | [progresses](https://developers.shoper.pl/developers/api/resources/progresses) |
| `promotionCode` | [promotion-codes](https://developers.shoper.pl/developers/api/resources/promotion-codes) |
| `redirect` | [redirects](https://developers.shoper.pl/developers/api/resources/redirects) |
| `shipping` | [shippings](https://developers.shoper.pl/developers/api/resources/shippings) |
| `specialOffer` | [special-offers](https://developers.shoper.pl/developers/api/resources/special-offers) |
| `status` | [statuses](https://developers.shoper.pl/developers/api/resources/statuses) |
| `subscriber` | [subscribers](https://developers.shoper.pl/developers/api/resources/subscribers) |
| `subscriberGroup` | [subscriber-groups](https://developers.shoper.pl/developers/api/resources/subscriber-groups) |
| `tax` | [taxes](https://developers.shoper.pl/developers/api/resources/taxs) |
| `unit` | [units](https://developers.shoper.pl/developers/api/resources/units) |
| `user` | [users](https://developers.shoper.pl/developers/api/resources/users) |
| `userAddress` | [user-addresses](https://developers.shoper.pl/developers/api/resources/user-addresses) |
| `userGroup` | [user-groups](https://developers.shoper.pl/developers/api/resources/user-groups) |
| `warehouse` | [warehouses](https://developers.shoper.pl/developers/api/resources/warehouses) |
| `warehouseLog` | [warehouse-logs](https://developers.shoper.pl/developers/api/resources/warehouse-logs) |
| `warehouseRelocation` | [warehouse-relocations](https://developers.shoper.pl/developers/api/resources/warehouse-relocations) |
| `webhook` | [webhooks](https://developers.shoper.pl/developers/api/resources/webhooks) |
| `zone` | [zones](https://developers.shoper.pl/developers/api/resources/zones) |
