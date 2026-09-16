<?php

namespace PanKrok\ShoperAppstoreBundle\Model\Resource;

use PanKrok\ShoperAppstoreBundle\Model\ResourceModel;

/**
 * Products of a collection and their order. Sub-resource: call setParent($collectionId) first.
 *
 *   $api->collectionProduct->setParent(3)->get();           // GET  collections/3/products
 *   $api->collectionProduct->setParent(3)->put(15, [...]);  // PUT  collections/3/products/15
 */
final class CollectionProduct extends ResourceModel
{
    protected string $url = 'collections/{parent}/products';
}
