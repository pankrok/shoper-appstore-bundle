<?php

namespace PanKrok\ShoperAppstoreBundle\Controller;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Response;

class AppstoreBillingController
{
    protected EventDispatcherInterface $dispatcher;
    protected array $config;

    public function __construct(EventDispatcherInterface $dispatcher, ParameterBagInterface $container)
    {
        $this->dispatcher = $dispatcher;
        $this->config     = $container->get('appstore');
    }

    public function init(array $request): Response
    {
        if (!isset($this->config['appstoreSecret'])) {
            return new Response('', Response::HTTP_BAD_REQUEST);
        }

        if (false === $this->checkHash($request, $this->config['appstoreSecret'])) {
            return new Response('', Response::HTTP_FORBIDDEN);
        }

        $preEventName = '\\PanKrok\\ShoperAppstoreBundle\\Events\\Pre' . $this->camelCase($request['action']) . 'Event';
        $eventName    = '\\PanKrok\\ShoperAppstoreBundle\\Events\\' . $this->camelCase($request['action']) . 'Event';

        if (class_exists($preEventName)) {
            $preEvent = new $preEventName($request);
            $this->dispatcher->dispatch($preEvent, $preEventName::NAME);
        }

        if (!class_exists($eventName)) {
            throw new \InvalidArgumentException('Event for action "' . $request['action'] . '" not found.');
        }

        $event = new $eventName($request);
        $this->dispatcher->dispatch($event, $eventName::NAME);

        return new Response('', Response::HTTP_OK);
    }

    private function checkHash(array $params, string $appstoreSecret): bool
    {
        $sentHash = $params['hash'];
        unset($params['hash']);
        ksort($params);

        $paramPairs = [];
        foreach ($params as $k => $v) {
            $paramPairs[] = $k . '=' . $v;
        }

        $hash = hash_hmac('sha512', implode('&', $paramPairs), $appstoreSecret);

        return hash_equals($hash, $sentHash);
    }

    private function camelCase(string $string): string
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));
    }
}
