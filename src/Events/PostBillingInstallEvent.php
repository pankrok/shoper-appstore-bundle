<?php

namespace PanKrok\ShoperAppstoreBundle\Events;

use Symfony\Contracts\EventDispatcher\Event;
use PanKrok\ShoperAppstoreBundle\Entity\Shops;

class PostBillingInstallEvent extends Event
{
    public const NAME = 'appstore.post_billing_install';
    protected Shops $shop;

    public function __construct(Shops $shop)
    {
        $this->shop = $shop;
    }

    public function getShop(): Shop
    {
        return $this->shop;
    }
}
