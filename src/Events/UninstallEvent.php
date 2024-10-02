<?php

namespace PanKrok\ShoperAppstoreBundle\Events;

use Symfony\Contracts\EventDispatcher\Event;

class UninstallEvent extends Event
{
    public const NAME = 'appstore.uninstall';
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
