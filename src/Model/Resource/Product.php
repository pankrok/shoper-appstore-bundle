<?php

namespace PanKrok\ShoperAppstoreBundle\Model\Resource;

use PanKrok\ShoperAppstoreBundle\Model\ResourceModel;

final class Product extends ResourceModel
{
    /** "type" field */
    public const TYPE_PRODUCT = 0;
    public const TYPE_BUNDLE  = 1;

    /** "stock.weight_type" — how the stock weight relates to the base product weight */
    public const WEIGHT_TYPE_NONE     = 0;
    public const WEIGHT_TYPE_NEW      = 1;
    public const WEIGHT_TYPE_ADD      = 2;
    public const WEIGHT_TYPE_SUBTRACT = 3;
    protected string $url = 'products';
}
