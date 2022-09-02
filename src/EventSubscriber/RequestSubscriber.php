<?php

namespace PanKrok\ShoperAppstoreBundle\EventSubscriber;

use PanKrok\ShoperAppstoreBundle\Controller\ApiController;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class RequestSubscriber implements EventSubscriberInterface
{
    protected $api;

    public function __construct(ApiController $api)
    {
        $this->api = $api;
    }

    public function onKernelRequest(RequestEvent $event)
    {
        if ($event->getRequest()->attributes->has('_route')) {
            $query = $event->getRequest()->query->all();
            $routeName = $event->getRequest()->attributes->get('_route');
            if (strpos($routeName, 'billing_') === false && $this->api->getAppId() === true) {
                $this->api->setParams($query);
            }
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            'kernel.request' => 'onKernelRequest',
        ];
    }
}
