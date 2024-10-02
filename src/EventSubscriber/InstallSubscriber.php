<?php

namespace PanKrok\ShoperAppstoreBundle\EventSubscriber;

use Doctrine\ORM\EntityManagerInterface;
use PanKrok\ShoperAppstoreBundle\Controller\API\Client;
use PanKrok\ShoperAppstoreBundle\Entity\AccessTokens;
use PanKrok\ShoperAppstoreBundle\Entity\Shops;
use PanKrok\ShoperAppstoreBundle\Events\InstallEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use PanKrok\ShoperAppstoreBundle\Repository\ShopsRepository;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class InstallSubscriber implements EventSubscriberInterface
{
    protected $em;
    protected $tokensEntity;
    protected $shopsRepository;
    protected $dispatcher;

    public function __construct(
        EventDispatcherInterface $dispatcher,
        EntityManagerInterface $entityManager,
        ParameterBagInterface $container,
        ShopsRepository $shopsRepository,
    ) {
        $this->dispatcher = $dispatcher;
        $this->em = $entityManager;
        $this->config = $container->get('appstore');
        $this->shopsRepository = $shopsRepository;
    }

    public function onInstallAction($event)
    {
        $payload = $event->getPayload();
        $options = [
            'options' => $this->config,
            'entrypoint' => $payload['shop_url'],
        ];

        $this->client = Client::factory(Client::ADAPTER_OAUTH, $options);
        $response = $this->client->auth($payload['auth_code'])->toArray();
        if (!isset($response['access_token'])) {
            throw new \Exception('Can\'t obtain access token');
        }

        if (($shop = $this->shopsRepository->findOneBy(['shop' => $payload['shop']])) !== null) {
            if (($token = $shop->getAccessTokens()) !== null) {
                $this->em->remove($token);
                $this->em->flush();
            }

            $shop->setInstalled(true);
        } else {
            $shop = new Shops();
            $shop->setInstalled(true);
            $shop->setCreatedAt(new \DatetimeImmutable('now'));
            $shop->setShop($payload['shop']);
            $shop->setShopUrl($payload['shop_url']);
            $shop->setVersion($payload['application_version']);
        }

        $this->em->persist($shop);
        $this->em->flush();

        $token = new AccessTokens();
        $token->setShop($shop);
        $token->setExpiresAt(new \DatetimeImmutable('now + 30days'));
        $token->setCreatedAt(new \DatetimeImmutable('now'));
        $token->setAccessToken($response['access_token']);
        $token->setRefreshToken($response['refresh_token']);

        $this->em->persist($token);
        $this->em->flush();

        $eventName = '\\PanKrok\\ShoperAppstoreBundle\\EventListener\\PostInstallEvent';
        $event = new $eventName($shop);
        $this->dispatcher->dispatch($event, $eventName::NAME);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            InstallEvent::NAME => 'onInstallAction',
        ];
    }
}
