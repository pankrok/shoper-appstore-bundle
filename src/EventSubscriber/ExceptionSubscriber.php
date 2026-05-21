<?php

namespace PanKrok\ShoperAppstoreBundle\EventSubscriber;

use PanKrok\ShoperAppstoreBundle\Exception\ShoperApiException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;

class ExceptionSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly Environment $twig)
    {
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if (!$exception instanceof ShoperApiException) {
            return;
        }

        $html = $this->twig->render('@Appstore/error.html.twig', [
            'status_code'       => $exception->getStatusCode(),
            'error'             => $exception->getShoperError(),
            'error_description' => $exception->getShoperErrorDescription(),
        ]);

        $event->setResponse(new Response($html, $exception->getStatusCode()));
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelException', 10],
        ];
    }
}
