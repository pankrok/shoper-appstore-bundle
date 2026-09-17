<?php

namespace PanKrok\ShoperAppstoreBundle\Model\Resource;

use PanKrok\ShoperAppstoreBundle\Model\ResourceModel;

final class Status extends ResourceModel
{
    /** "type" field of an order status */
    public const TYPE_NEW           = 1;
    public const TYPE_OPENED        = 2;
    public const TYPE_CLOSED        = 3;
    public const TYPE_NOT_COMPLETED = 4;
    protected string $url = 'statuses';
}
