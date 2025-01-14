<?php

namespace PanKrok\ShoperAppstoreBundle\Events;

use Symfony\Contracts\EventDispatcher\Event;

class PreUninstallEvent extends Event
{
    public const NAME = 'appstore.pre_uninstall';
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
