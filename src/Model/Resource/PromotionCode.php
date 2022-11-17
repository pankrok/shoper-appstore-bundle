<?php

namespace PanKrok\ShoperAppstoreBundle\Model\Resource;

use PanKrok\ShoperAppstoreBundle\Model\ResourceModel;

final class PromotionCode extends ResourceModel
{
    public const PERCENTAGE_DISCOUNT = 1;
    public const QUOTE_DISCOUNT = 2;
    public const FREE_DELIVERY_DISCOUNT = 3;
    public const PROGRESSIVE_PERCENTAGE_DISCOUNT = 4;
    public const PROGRESSIVE_QUOTE_DISCOUNT = 5;
	
	protected $url = 'promotion-codes';
}
