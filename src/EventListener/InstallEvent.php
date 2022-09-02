<?php

namespace PanKrok\ShoperAppstoreBundle\EventListener;

use Symfony\Contracts\EventDispatcher\Event;

class InstallEvent extends Event
{
    public const NAME = 'appstore.install';
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
