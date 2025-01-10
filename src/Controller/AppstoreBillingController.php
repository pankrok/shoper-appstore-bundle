<?php

namespace PanKrok\ShoperAppstoreBundle\Controller;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Response;

class AppstoreBillingController
{
    protected $dispatcher;
    protected $config;

    public function __construct(EventDispatcherInterface $dispatcher, ParameterBagInterface $container)
    {
        $this->dispatcher = $dispatcher;
        $this->config = $container->get('appstore');
    }

    public function init(array $request): Response
    {
        if (!isset($this->config['appstoreSecret'])) {
            return new Response(400);
        }
        
        if (true === self::checkHash($request, $this->config['appstoreSecret'])) {
            $eventName = '\\PanKrok\\ShoperAppstoreBundle\\Events\\'.$this->camelCase($request['action']).'Event';
            if (!class_exists($eventName)) {
                throw new \Exception('Event for action "'.$request['action'].'" not found.');
            }

            $event = new $eventName($request);
            $this->dispatcher->dispatch($event, $eventName::NAME);

            return new Response(200);
        }

        return new Response(403);
    }

    protected function checkHash(array $params, string $appstoreSecret): bool
    {
        $send_hash = $params['hash'];
        unset($params['hash']);
        ksort($params);
        $param_array = [];
        foreach ($params as $k => $v) {
            $param_array[] = $k.'='.$v;
        }

        $param_string = join('&', $param_array);
        $hash = hash_hmac('sha512', $param_string, $appstoreSecret);
        if ($send_hash === $hash) {
            return true;
        }

        return false;
    }

    protected function camelCase(string $string, bool $capitalizeFirstCharacter = true): string
    {
        $str = str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));

        return $str;
    }
}
