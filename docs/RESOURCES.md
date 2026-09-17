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

### Pagination

Collection responses are `{count, pages, page, list}`. `ResponseModel` exposes them directly:

```php
$response = $api->product->setLimit(50)->setPage(2)->get();

$response->getList();   // array of objects on this page
$response->getCount();  // total matching objects
$response->getPages();  // total pages
$response->getPage();   // current page (1-based)
```

`iterate()` walks every page for you and yields single objects. Filters, order and limit are honoured; the client's rate-limit handling keeps you under the shop's quota:

```php
foreach ($api->product->setFilters(['stock.active' => 1])->setLimit(50)->iterate() as $product) {
    // $product is one array from "list"
}
```

Use `setOffset($n)` instead of `setPage()` when you need a record index — it replaces `page` in the query.

### Filters

`setFilters()` accepts the documented array syntax or a `Filter` builder, which makes operators explicit and merges several conditions on one field:

```php
use PanKrok\ShoperAppstoreBundle\Model\Filter;
use PanKrok\ShoperAppstoreBundle\Model\Resource\Product;

$products = $api->product
    ->setFilters(
        Filter::create()
            ->eq('translations.pl_PL.active', true)
            ->like('translations.pl_PL.name', 'z%')      // "%" is the wildcard
            ->between('stock.price', 10, 20)             // >= 10 AND <= 20
            ->in('category_id', [3, 4])
            ->neq('type', Product::TYPE_BUNDLE)
    )
    ->iterate();
```

Available: `eq`, `neq`, `gt`, `gte`, `lt`, `lte`, `between`, `like`, `notLike`, `in`, `notIn`, plus `where($field, $operator, $value)` for raw operator names (`~` / `!~` aliases are accepted). Unknown operators throw. Nested fields use dot notation (`translations.pl_PL.name`).

### Sub-resources

Resources nested under a parent object need `setParent($id)` before the request:

```php
$api->collectionProduct->setParent($collectionId)->get();
$api->collectionProduct->setParent($collectionId)->put($productId, ['position' => 3]);
$api->paymentChannel->setParent($paymentId)->get();
```

Calling a sub-resource without a parent throws `LogicException`.

### Domain constants

Enum-like fields documented by Shoper are exposed as constants on the resource classes:

| Class | Constants |
|---|---|
| `Product` | `TYPE_PRODUCT`, `TYPE_BUNDLE`, `WEIGHT_TYPE_NONE / NEW / ADD / SUBTRACT` |
| `Status` | `TYPE_NEW`, `TYPE_OPENED`, `TYPE_CLOSED`, `TYPE_NOT_COMPLETED` |
| `Order` | `STATUS_TYPE_*` (aliases of `Status::TYPE_*`), `ORIGIN_SHOP / FACEBOOK / MOBILE / ALLEGRO / WEBAPI / ADMIN_PANEL / ADMIN_AUTHENTICATED / GOOGLE`, `ORIGIN_APILO_MIN..MAX` |
| `Attribute` | `TYPE_TEXT`, `TYPE_CHECKBOX`, `TYPE_SELECT` |
| `Shipping` | `DEPEND_ON_NONE / WEIGHT / ORDER_AMOUNT / PRODUCTS_QUANTITY / GAUGE_WEIGHT` |
| `AdditionalField` | `TYPE_TEXT..TYPE_DESCRIPTION`, `LOCATE_*` bitmask (combine with `\|`) |
| `Metafield` | `TYPE_INT / FLOAT / STRING / BLOB`, `OBJECT_*` for every documented object name, `OBJECTS` list |

```php
$open = $api->order->setFilters(Filter::create()->eq('status.type', Order::STATUS_TYPE_OPENED))->get();

$api->metafield->setObject(Metafield::OBJECT_PRODUCT)->get();
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
| `collectionProduct` | [collections/{id}/products](https://developers.shoper.pl/developers/api/resources/collections-products) — sub-resource |
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
| `orderRefund` | [order-refunds](https://developers.shoper.pl/developers/api/resources/order-refunds) |
| `orderTransaction` | [order-transactions](https://developers.shoper.pl/developers/api/resources/order-transactions) |
| `orderTag` | [order-tags](https://developers.shoper.pl/developers/api/resources/order-tags) |
| `parcel` | [parcels](https://developers.shoper.pl/developers/api/resources/parcels) |
| `payment` | [payments](https://developers.shoper.pl/developers/api/resources/payments) |
| `paymentChannel` | [payments/{id}/channels](https://developers.shoper.pl/developers/api/resources/payments-channels) — sub-resource, selected apps only |
| `producer` | [producers](https://developers.shoper.pl/developers/api/resources/producers) |
| `product` | [products](https://developers.shoper.pl/developers/api/resources/products) |
| `productFile` | [product-files](https://developers.shoper.pl/developers/api/resources/product-files) |
| `productImage` | [product-images](https://developers.shoper.pl/developers/api/resources/product-images) |
| `productSafetyCertificate` | [product-safety-certificates](https://developers.shoper.pl/developers/api/resources/product-safety-certificates) (GPSR) |
| `productSafetyImporter` | [product-safety-importers](https://developers.shoper.pl/developers/api/resources/product-safety-importers) (GPSR) |
| `productSafetyProducer` | [product-safety-producers](https://developers.shoper.pl/developers/api/resources/product-safety-producers) (GPSR) |
| `productSafetyResponsible` | [product-safety-responsibles](https://developers.shoper.pl/developers/api/resources/product-safety-responsibles) (GPSR) |
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
