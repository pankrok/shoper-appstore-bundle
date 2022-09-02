<?php

namespace PanKrok\ShoperAppstoreBundle\EventListener;

use Symfony\Contracts\EventDispatcher\Event;

class BillingSubscriptionEvent extends Event
{
    public const NAME = 'appstore.billing_subscription';
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
