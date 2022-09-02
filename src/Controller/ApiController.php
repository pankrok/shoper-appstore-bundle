<?php

namespace PanKrok\ShoperAppstoreBundle\Controller;

use PanKrok\ShoperAppstoreBundle\Controller\API\Client;
use PanKrok\ShoperAppstoreBundle\Repository\ShopsRepository;
use PanKrok\ShoperAppstoreBundle\Controller\API\Client\BearerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ApiController
{
    protected $client;
    protected $apiOptions = [];
    protected $params;
    protected $shopUrl = null;
    protected $twig;

    public function __construct(ParameterBagInterface $container, ShopsRepository $shopsRepository)
    {
        $this->apiOptions = $container->get('appstore');
        $this->shopsRepository = $shopsRepository;
        if (isset($this->apiOptions['shopurl'])) {
            $this->shopUrl = $this->apiOptions['shopurl'];
        }
    }

    public function __get($property)
    {
        $property = ucfirst($property);
        if ($property === 'Bulk') {
            $property .= 'Model';
            $class = "\\PanKrok\\ShoperAppstoreBundle\\Model\\$property";
        } else {
            $class = "\\PanKrok\\ShoperAppstoreBundle\\Model\\Resource\\$property";
        }
        
        if (class_exists($class)) {
            return new $class($this->client);         
        }
    }

    public function setParams(array $params, bool $checkHash = true): void
    {
        if (isset($params['shop'])) {
            $this->params = $params;
            if (false === $this->checkHash($checkHash)) {
                throw new \Exception('Invalid hash');
            }

            $shop = $this->shopsRepository->findOneBy(['shop' => $params['shop']]);
            $this->shopUrl = $shop->getShopUrl();
            $token = $shop->getAccessTokens();

            $options = [
                'options' => $this->apiOptions,
                'entrypoint' => $this->shopUrl,
            ];

            $this->client = Client::factory(Client::ADAPTER_OAUTH, $options);
            $this->client->setToken($token->getAccessToken());
        } 
    }
    
    public function setBasicAuth(string $url = null, array $basicAuth = []): BearerInterface 
    {
        if (!empty($basicAuth)) {
            $this->apiOptions = $basicAuth;
        }
        
        if (isset($url)) {
            $this->shopUrl = $url;
        }
        
        $options = [
            'options' =>$this->apiOptions,
            'entrypoint' => $this->shopUrl,
        ];

        $this->client = Client::factory(Client::ADAPTER_BASIC_AUTH, $options);
        return $this->client;
    }

    public function getParams()
    {
        return $this->params;
    }

     public function setClient(BearerInterface $client): void
    {
        $this->client = $client;
    }

    public function getClient(): BearerInterface
    {
        return $this->client;
    }
    

    public function setShopUrl(string $url): void
    {
        $this->shopUrl = $url;
    }

    public function getShopUrl(): ?string
    {
        return $this->shopUrl;
    }

    public function getAppId(): bool
    {
        return isset($this->apiOptions['appId']);
    }

    protected function checkHash(bool $checkHash): bool
    {
        if (false === $checkHash) {
            return true;
        }

        $params = [
            'place' => $this->params['place'],
            'shop' => $this->params['shop'],
            'timestamp' => $this->params['timestamp'],
        ];

        $send_hash = $this->params['hash'];
        ksort($params);
        $param_array = [];
        foreach ($params as $k => $v) {
            $param_array[] = $k.'='.$v;
        }
        $param_string = join('&', $param_array);
        $hash = hash_hmac('sha512', $param_string, $this->apiOptions['appstoreSecret']);
        if ($hash === $send_hash) {
            return true;
        }

        return false;
    }
}