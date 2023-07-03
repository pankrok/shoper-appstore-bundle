<?php

namespace PanKrok\ShoperAppstoreBundle\Controller;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use PanKrok\ShoperAppstoreBundle\Repository\ShopsRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;

class WebhookController
{
    protected $options;
    protected $container;
    protected $shopRepository;
    protected $api;
    protected $em;

    public function __construct(ParameterBagInterface $container, ShopsRepository $shopRepository, EntityManagerInterface $em)
    {
        $this->container = $container;
        $this->options = $container->get('appstore');
        $this->shopRepository = $shopRepository;
        $this->em = $em;
    }

    public function checksum(Request &$request, string $secret, bool $appstore = true): Request
    {
        $server = $request->server->all();
        $data = $request->getContent();

        if (true === $appstore && isset($server['HTTP_X_SHOP_LICENSE'])) {
            $secret_key = hash_hmac('sha512', $server['HTTP_X_SHOP_LICENSE'].':'.$secret, $this->options['appstoreSecret']);
        }

        if (empty($data) || !isset($server['HTTP_X_WEBHOOK_ID']) || !isset($server['HTTP_X_WEBHOOK_SHA1']) || sha1($server['HTTP_X_WEBHOOK_ID'].':'.$secret_key.':'.$data) !== $server['HTTP_X_WEBHOOK_SHA1']) {
            throw new \Exception('invalid checksum');
        }

        $this->api = new ApiController($this->container, $this->shopRepository, $this->em);
        $this->api->setParams(['shop' => $server['HTTP_X_SHOP_LICENSE']], false);

        return $request;
    }
    
    public function getApiClient(): ?ApiController
    {
        return $this->api;
    }
}
