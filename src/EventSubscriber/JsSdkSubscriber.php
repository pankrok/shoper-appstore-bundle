<?php

namespace PanKrok\ShoperAppstoreBundle\EventSubscriber;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Twig\Environment;

class JsSdkSubscriber implements EventSubscriberInterface
{
    protected $twig;
    protected $sdk;

    public function __construct(Environment $twig, ParameterBagInterface $bag)
    {
        $this->twig = $twig;
        $this->sdk = $bag->get('appstore')['jssdk'];
    }

    public function onControllerEvent(ControllerEvent $event)
    {
        $this->twig->addGlobal('shoper_js_sdk', $this->sdk);
    }

    public static function getSubscribedEvents()
    {
        return [
            ControllerEvent::class => 'onControllerEvent',
        ];
    }
}
