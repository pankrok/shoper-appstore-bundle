<?php

namespace PanKrok\ShoperAppstoreBundle\Events;

use Symfony\Contracts\EventDispatcher\Event;

class PreUpgradeEvent extends Event
{
    public const NAME = 'appstore.pre_upgrade';
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
