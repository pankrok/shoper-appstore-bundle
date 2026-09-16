<?php

namespace PanKrok\ShoperAppstoreBundle\Model\Resource;

use PanKrok\ShoperAppstoreBundle\Model\ResourceModel;

final class Shipping extends ResourceModel
{
    /** "depend_on_w" — what the shipping cost depends on */
    public const DEPEND_ON_NONE              = 0;
    public const DEPEND_ON_WEIGHT            = 1;
    public const DEPEND_ON_ORDER_AMOUNT      = 2;
    public const DEPEND_ON_PRODUCTS_QUANTITY = 3;
    public const DEPEND_ON_GAUGE_WEIGHT      = 4;
    protected string $url = 'shippings';
}
