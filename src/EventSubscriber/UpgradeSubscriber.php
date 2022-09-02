<?php

namespace PanKrok\ShoperAppstoreBundle\EventSubscriber;

use Doctrine\ORM\EntityManagerInterface;
use PanKrok\ShoperAppstoreBundle\Controller\API\Client;
use PanKrok\ShoperAppstoreBundle\Entity\AccessTokens;
use PanKrok\ShoperAppstoreBundle\EventListener\UpgradeEvent;
use PanKrok\ShoperAppstoreBundle\Repository\ShopsRepository;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class UpgradeSubscriber implements EventSubscriberInterface
{
    protected $client;
    protected $em;
    protected $tokensEntity;
    protected $shopsRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        ParameterBagInterface $container,
        ShopsRepository $shopsRepository,
    ) {
        $this->em = $entityManager;
        $this->config = $container->get('appstore');
        $this->shopsRepository = $shopsRepository;
    }

    public function onUpgradeAction($event)
    {
        $payload = $event->getPayload();
        $options = [
            'appstore' => $this->config,
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
            throw new \Exception('InstallSubscriber error: shop not found');
        }

        $this->em->persist($shop);
        $this->em->flush();

        $token = new AccessTokens();
        $token->setShop($shop);
        $token->setExpiresAt(new \DateTime('now + 30days'));
        $token->setCreatedAt(new \DateTime('now'));
        $token->setAccessToken($response['access_token']);
        $token->setRefreshToken($response['refresh_token']);

        $this->em->persist($token);
        $this->em->flush();
    }

    public static function getSubscribedEvents(): array
    {
        return [
            UpgradeEvent::NAME => 'onUpgradeAction',
        ];
    }
}
