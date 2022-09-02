<?php

namespace PanKrok\ShoperAppstoreBundle\EventSubscriber;

use Doctrine\ORM\EntityManagerInterface;
use PanKrok\ShoperAppstoreBundle\EventListener\UninstallEvent;
use PanKrok\ShoperAppstoreBundle\Repository\ShopsRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UninstallSubscriber implements EventSubscriberInterface
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

    public function onUninstallAction($event)
    {
        $payload = $event->getPayload();
        if (($shop = $this->shopsRepository->findOneBy(['shop' => $payload['shop']])) !== null) {
            if (($token = $shop->getAccessTokens()) !== null) {
                $this->em->remove($token);
                $this->em->flush();
            }

            $shop->setAccessTokens(null);
            $shop->setInstalled(false);
            $this->em->persist($shop);
            $this->em->flush();
        } else {
            // debug log
            throw new \Exception('Can\'t find '.$payload['shop'].' shop in database');
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            UninstallEvent::NAME => 'onUninstallAction',
        ];
    }
}
