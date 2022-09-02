<?php

namespace PanKrok\ShoperAppstoreBundle\EventListener;

use Symfony\Contracts\EventDispatcher\Event;

class UpgradeEvent extends Event
{
    public const NAME = 'appstore.upgrade';
    protected $payload;

    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    public function getPayload()
    {
        return $this->payload;
    }
}
