# Resources

below you will find a list of all available resources in sdk

* [aboutPage](https://developers.shoper.pl/developers/api/resources/aboutpages)
* [additionalField](https://developers.shoper.pl/developers/api/resources/additional-fields)
* [applicationConfig](https://developers.shoper.pl/developers/api/resources/application-config)
* [applicationLock](https://developers.shoper.pl/developers/api/resources/application-lock)
* [applicationVersion](https://developers.shoper.pl/developers/api/resources/application-version)
* [attribute](https://developers.shoper.pl/developers/api/resources/attributes)
* [attributeGroup](https://developers.shoper.pl/developers/api/resources/attribute-groups)
* [auction](https://developers.shoper.pl/developers/api/resources/auctions)
* [auctionHouse](https://developers.shoper.pl/developers/api/resources/auction-houses)
* [auctionOrder](https://developers.shoper.pl/developers/api/resources/auction-orders)
* [availability](https://developers.shoper.pl/developers/api/resources/availabilitys)
* [categoriesTree](https://developers.shoper.pl/developers/api/resources/categories-tree)
* [category](https://developers.shoper.pl/developers/api/resources/categorys)
* [collection](https://developers.shoper.pl/developers/api/resources/collections)
* [currency](https://developers.shoper.pl/developers/api/resources/currencys)
* [dashboardActivity](https://developers.shoper.pl/developers/api/resources/dashboard-activitys)
* [dashboardStat](https://developers.shoper.pl/developers/api/resources/dashboard-stats)
* [delivery](https://developers.shoper.pl/developers/api/resources/deliveries)
* [gauge](https://developers.shoper.pl/developers/api/resources/gauges)
* [geolocationCountry](https://developers.shoper.pl/developers/api/resources/geolocation-countrys)
* [geolocationRegion](https://developers.shoper.pl/developers/api/resources/geolocation-regions)
* [geolocationSubregion](https://developers.shoper.pl/developers/api/resources/geolocation-subregions)
* [language](https://developers.shoper.pl/developers/api/resources/languages)
* [loyaltyEvent](https://developers.shoper.pl/developers/api/resources/loyalty-events)
* [metafield](https://developers.shoper.pl/developers/api/resources/metafields)
* [metafieldValue](https://developers.shoper.pl/developers/api/resources/metafield-values)
* [news](https://developers.shoper.pl/developers/api/resources/news)
* [newsCategory](https://developers.shoper.pl/developers/api/resources/news-categorys)
* [newsComment](https://developers.shoper.pl/developers/api/resources/news-comments)
* [newsFile](https://developers.shoper.pl/developers/api/resources/news-files)
* [newsTag](https://developers.shoper.pl/developers/api/resources/news-tags)
* [objectMtime](https://developers.shoper.pl/developers/api/resources/object-mtime)
* [option](https://developers.shoper.pl/developers/api/resources/options)
* [optionGroup](https://developers.shoper.pl/developers/api/resources/option-groups)
* [optionValue](https://developers.shoper.pl/developers/api/resources/option-values)
* [order](https://developers.shoper.pl/developers/api/resources/orders)
* [orderProduct](https://developers.shoper.pl/developers/api/resources/order-products)
* [orderTag](https://developers.shoper.pl/developers/api/resources/order-tags)
* [parcel](https://developers.shoper.pl/developers/api/resources/parcels)
* [payment](https://developers.shoper.pl/developers/api/resources/payments)
* [producer](https://developers.shoper.pl/developers/api/resources/producers)
* [product](https://developers.shoper.pl/developers/api/resources/products)
* [productFile](https://developers.shoper.pl/developers/api/resources/product-files)
* [productImage](https://developers.shoper.pl/developers/api/resources/product-images)
* [productStock](https://developers.shoper.pl/developers/api/resources/product-stocks)
* [productTag](https://developers.shoper.pl/developers/api/resources/product-tags)
* [progress](https://developers.shoper.pl/developers/api/resources/progresses)
* [promotionCode](https://developers.shoper.pl/developers/api/resources/promotion-codes)
* [redirect](https://developers.shoper.pl/developers/api/resources/redirects)
* [shipping](https://developers.shoper.pl/developers/api/resources/shippings)
* [specialOffer](https://developers.shoper.pl/developers/api/resources/special-offers)
* [status](https://developers.shoper.pl/developers/api/resources/statuses)
* [subscriber](https://developers.shoper.pl/developers/api/resources/subscribers)
* [subscriberGroup](https://developers.shoper.pl/developers/api/resources/subscriber-groups)
* [tax](https://developers.shoper.pl/developers/api/resources/taxs)
* [unit](https://developers.shoper.pl/developers/api/resources/units)
* [user](https://developers.shoper.pl/developers/api/resources/users)
* [userAddress](https://developers.shoper.pl/developers/api/resources/user-addresses)
* [userGroup](https://developers.shoper.pl/developers/api/resources/user-groups)
* [warehouse](https://developers.shoper.pl/developers/api/resources/warehouses)
* [warehouseLog](https://developers.shoper.pl/developers/api/resources/warehouse-logs)
* [warehouseRelocation](https://developers.shoper.pl/developers/api/resources/warehouse-relocations)
* [webhook](https://developers.shoper.pl/developers/api/resources/webhooks)
* [zone](https://developers.shoper.pl/developers/api/resources/zones)

>example of getting resource:

```php
<?php

    public function index(ApiController $api): Response
    {
        $productResource = $api->product
        
    // rest of code...
```