<?php

namespace PanKrok\ShoperAppstoreBundle\Model\Resource;

use PanKrok\ShoperAppstoreBundle\Model\ResourceModel;

final class Order extends ResourceModel
{
    /** "status.type" — see Status::TYPE_* */
    public const STATUS_TYPE_NEW           = Status::TYPE_NEW;
    public const STATUS_TYPE_OPENED        = Status::TYPE_OPENED;
    public const STATUS_TYPE_CLOSED        = Status::TYPE_CLOSED;
    public const STATUS_TYPE_NOT_COMPLETED = Status::TYPE_NOT_COMPLETED;

    /** "origin" field — where the order was placed */
    public const ORIGIN_SHOP                = 0;
    public const ORIGIN_FACEBOOK            = 1;
    public const ORIGIN_MOBILE              = 2;
    public const ORIGIN_ALLEGRO             = 3;
    public const ORIGIN_WEBAPI              = 4;
    public const ORIGIN_ADMIN_PANEL         = 5;
    public const ORIGIN_ADMIN_AUTHENTICATED = 6;
    public const ORIGIN_GOOGLE              = 8;
    /** Apilo integrations use the 100–111 range */
    public const ORIGIN_APILO_MIN = 100;
    public const ORIGIN_APILO_MAX = 111;
    protected string $url = 'orders';
}
