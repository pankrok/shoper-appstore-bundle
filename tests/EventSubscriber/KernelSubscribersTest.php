<?php

namespace PanKrok\ShoperAppstoreBundle\Tests\EventSubscriber;

use PanKrok\ShoperAppstoreBundle\Controller\ApiController;
use PanKrok\ShoperAppstoreBundle\EventSubscriber\ExceptionSubscriber;
use PanKrok\ShoperAppstoreBundle\EventSubscriber\JsSdkSubscriber;
use PanKrok\ShoperAppstoreBundle\EventSubscriber\RequestSubscriber;
use PanKrok\ShoperAppstoreBundle\Exception\ShoperApiException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;

final class KernelSubscribersTest extends TestCase
{
    private function kernel(): HttpKernelInterface
    {
        return $this->createMock(HttpKernelInterface::class);
    }

    // --- ExceptionSubscriber -----------------------------------------------------

    public function testRendersErrorPageForShoperApiException(): void
    {
        $twig = $this->createMock(Environment::class);
        $twig->expects(self::once())->method('render')
            ->with('@Appstore/error.html.twig', ['status_code' => 403, 'error' => 'insufficient_scope', 'error_description' => 'no products_read'])
            ->willReturn('<h1>403</h1>');

        $event = new ExceptionEvent($this->kernel(), new Request(), HttpKernelInterface::MAIN_REQUEST,
            new ShoperApiException(403, 'insufficient_scope', 'no products_read'));

        (new ExceptionSubscriber($twig))->onKernelException($event);

        self::assertNotNull($event->getResponse());
        self::assertSame(403, $event->getResponse()->getStatusCode());
        self::assertSame('<h1>403</h1>', $event->getResponse()->getContent());
    }

    public function testIgnoresOtherExceptions(): void
    {
        $twig = $this->createMock(Environment::class);
        $twig->expects(self::never())->method('render');

        $event = new ExceptionEvent($this->kernel(), new Request(), HttpKernelInterface::MAIN_REQUEST, new \RuntimeException('boom'));
        (new ExceptionSubscriber($twig))->onKernelException($event);

        self::assertNull($event->getResponse());
    }

    public function testExceptionSubscriberRunsBeforeDefaultHandler(): void
    {
        self::assertSame([KernelEvents::EXCEPTION => ['onKernelException', 10]], ExceptionSubscriber::getSubscribedEvents());
    }

    // --- JsSdkSubscriber ---------------------------------------------------------

    public function testJsSdkGlobalIsRegisteredOnController(): void
    {
        $twig = $this->createMock(Environment::class);
        $twig->expects(self::once())->method('addGlobal')->with('shoper_js_sdk', 'https://cdn/sdk.js');

        $subscriber = new JsSdkSubscriber($twig, new ParameterBag(['appstore' => ['jssdk' => 'https://cdn/sdk.js']]));
        $subscriber->onControllerEvent(new ControllerEvent($this->kernel(), fn() => null, new Request(), HttpKernelInterface::MAIN_REQUEST));

        self::assertSame([ControllerEvent::class => 'onControllerEvent'], JsSdkSubscriber::getSubscribedEvents());
    }

    // --- RequestSubscriber -------------------------------------------------------

    private function requestEvent(?string $route, array $query = []): RequestEvent
    {
        $request = new Request($query);
        if (null !== $route) {
            $request->attributes->set('_route', $route);
        }

        return new RequestEvent($this->kernel(), $request, HttpKernelInterface::MAIN_REQUEST);
    }

    public function testInitialisesApiFromIframeQueryInOAuthMode(): void
    {
        $api = $this->createMock(ApiController::class);
        $api->method('isOAuthMode')->willReturn(true);
        $api->expects(self::once())->method('initFromRequest')->with(['shop' => 's', 'hash' => 'h']);

        (new RequestSubscriber($api))->onKernelRequest($this->requestEvent('app_dashboard', ['shop' => 's', 'hash' => 'h']));
    }

    public function testSkipsBillingRoutes(): void
    {
        $api = $this->createMock(ApiController::class);
        $api->method('isOAuthMode')->willReturn(true);
        $api->expects(self::never())->method('initFromRequest');

        (new RequestSubscriber($api))->onKernelRequest($this->requestEvent('billing_app_demo', ['shop' => 's']));
    }

    public function testSkipsBasicAuthModeAndUnroutedRequests(): void
    {
        $api = $this->createMock(ApiController::class);
        $api->method('isOAuthMode')->willReturn(false);
        $api->expects(self::never())->method('initFromRequest');

        $subscriber = new RequestSubscriber($api);
        $subscriber->onKernelRequest($this->requestEvent('app_dashboard', ['shop' => 's']));
        $subscriber->onKernelRequest($this->requestEvent(null, ['shop' => 's']));

        self::assertSame(['kernel.request' => 'onKernelRequest'], RequestSubscriber::getSubscribedEvents());
    }
}
