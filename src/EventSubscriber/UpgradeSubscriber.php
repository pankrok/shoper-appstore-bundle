<?php

namespace PanKrok\ShoperAppstoreBundle\EventSubscriber;

use Doctrine\ORM\EntityManagerInterface;
use PanKrok\ShoperAppstoreBundle\Events\UpgradeEvent;
use PanKrok\ShoperAppstoreBundle\Repository\ShopsRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UpgradeSubscriber implements EventSubscriberInterface
{
    protected $em;
    protected $shopsRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        ShopsRepository $shopsRepository,
    ) {
        $this->em = $entityManager;
        $this->shopsRepository = $shopsRepository;
    }

    public function onUpgradeAction($event)
    {
        $payload = $event->getPayload();

        $shop = $this->shopsRepository->findOneBy(['shop' => $payload['shop']]);
        if ($shop === null) {
            throw new \Exception('UpgradeSubscriber error: shop not found');
        }

        $shop->setVersion($payload['application_version']);
        $shop->setShopUrl($payload['shop_url']);

        $this->em->persist($shop);
        $this->em->flush();
    }

    public static function getSubscribedEvents(): array
    {
        return [
            UpgradeEvent::NAME => 'onUpgradeAction',
        ];
    }
}
