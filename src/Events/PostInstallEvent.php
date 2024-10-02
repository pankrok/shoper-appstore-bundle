<?php

namespace PanKrok\ShoperAppstoreBundle\Events;

use Symfony\Contracts\EventDispatcher\Event;
use PanKrok\ShoperAppstoreBundle\Entity\Shops;

class PostInstallEvent extends Event
{
    public const NAME = 'appstore.post_install';
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
