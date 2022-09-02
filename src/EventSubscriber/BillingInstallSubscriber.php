<?php

namespace PanKrok\ShoperAppstoreBundle\EventSubscriber;

use Doctrine\ORM\EntityManagerInterface;
use PanKrok\ShoperAppstoreBundle\Entity\Billings;
use PanKrok\ShoperAppstoreBundle\EventListener\BillingInstallEvent;
use PanKrok\ShoperAppstoreBundle\Repository\ShopsRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class BillingInstallSubscriber implements EventSubscriberInterface
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

    public function onBillingInstall($event)
    {
        $payload = $event->getPayload();
        if (($shop = $this->shopsRepository->findOneBy(['shop' => $payload['shop']])) !== null) {
            $billing = new Billings();
            $billing->setShop($shop);
            $billing->setCreatedAt(new \Datetime('now'));
            $this->em->persist($billing);
            $this->em->flush();
        } else {
            // debug log
            throw new \Exception('Can\'t find '.$payload['shop'].' shop in database');
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            BillingInstallEvent::NAME => 'onBillingInstall',
        ];
    }
}
