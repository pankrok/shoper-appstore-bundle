<?php

namespace PanKrok\ShoperAppstoreBundle\Model\Resource;

use PanKrok\ShoperAppstoreBundle\Model\ResourceModel;

/**
 * Payment transactions attached to orders.
 */
final class OrderTransaction extends ResourceModel
{
    protected string $url = 'order-transactions';
}
