<?php

namespace PanKrok\ShoperAppstoreBundle\Model\Resource;

use PanKrok\ShoperAppstoreBundle\Model\ResourceModel;

/**
 * Refunds issued for orders.
 */
final class OrderRefund extends ResourceModel
{
    protected string $url = 'order-refunds';
}
