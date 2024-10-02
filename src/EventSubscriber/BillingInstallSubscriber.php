<?php

namespace PanKrok\ShoperAppstoreBundle\EventSubscriber;

use Doctrine\ORM\EntityManagerInterface;
use PanKrok\ShoperAppstoreBundle\Entity\Billings;
use PanKrok\ShoperAppstoreBundle\Events\BillingInstallEvent;
use PanKrok\ShoperAppstoreBundle\Repository\ShopsRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class BillingInstallSubscriber implements EventSubscriberInterface
{
    protected $em;
    protected $shopsRepository;
    protected $dispatcher;

    public function __construct(
        EventDispatcherInterface $dispatcher,
        EntityManagerInterface $entityManager,
        ShopsRepository $shopsRepository,
    ) {
        $this->dispatcher = $dispatcher;
        $this->em = $entityManager;
        $this->shopsRepository = $shopsRepository;
    }

    public function onBillingInstall($event)
    {
        $eventName = '\\PanKrok\\ShoperAppstoreBundle\\EventListener\\PostBillingInstallEvent';
        $payload = $event->getPayload();
        if (($shop = $this->shopsRepository->findOneBy(['shop' => $payload['shop']])) !== null) {
            $billing = new Billings();
            $billing->setShop($shop);
            $billing->setCreatedAt(new \DatetimeImmutable('now'));
            $this->em->persist($billing);
            $this->em->flush();

            $event = new $eventName($shop);
            $this->dispatcher->dispatch($event, $eventName::NAME);
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
