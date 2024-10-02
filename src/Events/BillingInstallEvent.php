<?php

namespace PanKrok\ShoperAppstoreBundle\Events;

use Symfony\Contracts\EventDispatcher\Event;

class BillingInstallEvent extends Event
{
    public const NAME = 'appstore.billing_install';
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
