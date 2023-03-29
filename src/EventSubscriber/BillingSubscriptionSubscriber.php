<?php

namespace PanKrok\ShoperAppstoreBundle\EventSubscriber;

use Doctrine\ORM\EntityManagerInterface;
use PanKrok\ShoperAppstoreBundle\Entity\Subscriptions;
use PanKrok\ShoperAppstoreBundle\EventListener\BillingSubscriptionEvent;
use PanKrok\ShoperAppstoreBundle\Repository\ShopsRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class BillingSubscriptionSubscriber implements EventSubscriberInterface
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

    public function onBillingSubscription($event)
    {
        $payload = $event->getPayload();
        if (($shop = $this->shopsRepository->findOneBy(['shop' => $payload['shop']])) !== null) {
            $subscribtion = new Subscriptions();
            $subscribtion->setShop($shop);
            $subscribtion->setExpiresAt(new \Datetime($payload['subscription_end_time']));
            $this->em->persist($subscribtion);
            $this->em->flush();
        } else {
            // debug log
            throw new \Exception('Can\'t find '.$payload['shop'].' shop in database');
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            BillingSubscriptionEvent::NAME => 'onBillingSubscription',
        ];
    }
}
