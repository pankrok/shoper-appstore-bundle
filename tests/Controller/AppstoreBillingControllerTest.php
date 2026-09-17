<?php

namespace PanKrok\ShoperAppstoreBundle\Tests\Controller;

use PanKrok\ShoperAppstoreBundle\Controller\AppstoreBillingController;
use PanKrok\ShoperAppstoreBundle\Events\BillingInstallEvent;
use PanKrok\ShoperAppstoreBundle\Events\BillingSubscriptionEvent;
use PanKrok\ShoperAppstoreBundle\Events\InstallEvent;
use PanKrok\ShoperAppstoreBundle\Events\PreBillingInstallEvent;
use PanKrok\ShoperAppstoreBundle\Events\PreBillingSubscriptionEvent;
use PanKrok\ShoperAppstoreBundle\Events\PreInstallEvent;
use PanKrok\ShoperAppstoreBundle\Events\PreUninstallEvent;
use PanKrok\ShoperAppstoreBundle\Events\PreUpgradeEvent;
use PanKrok\ShoperAppstoreBundle\Events\UninstallEvent;
use PanKrok\ShoperAppstoreBundle\Events\UpgradeEvent;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Contracts\EventDispatcher\Event;

final class AppstoreBillingControllerTest extends TestCase
{
    private const SECRET = 'appstore-secret';

    private EventDispatcher $dispatcher;
    /** @var array<int, array{name: string, event: Event}> */
    private array $dispatched = [];

    protected function setUp(): void
    {
        $this->dispatcher = new EventDispatcher();
        foreach ([
            PreInstallEvent::class, InstallEvent::class,
            PreUninstallEvent::class, UninstallEvent::class,
            PreUpgradeEvent::class, UpgradeEvent::class,
            PreBillingInstallEvent::class, BillingInstallEvent::class,
            PreBillingSubscriptionEvent::class, BillingSubscriptionEvent::class,
        ] as $class) {
            $this->dispatcher->addListener($class::NAME, function (Event $e, string $name): void {
                $this->dispatched[] = ['name' => $name, 'event' => $e];
            });
        }
    }

    private function controller(?string $secret = self::SECRET): AppstoreBillingController
    {
        $config = $secret === null ? ['appId' => 'x'] : ['appId' => 'x', 'appstoreSecret' => $secret];

        return new AppstoreBillingController($this->dispatcher, new ParameterBag(['appstore' => $config]));
    }

    private static function sign(array $params, string $secret = self::SECRET): array
    {
        ksort($params);
        $pairs = [];
        foreach ($params as $k => $v) {
            $pairs[] = "$k=$v";
        }
        $params['hash'] = hash_hmac('sha512', implode('&', $pairs), $secret);

        return $params;
    }

    public static function actions(): iterable
    {
        yield 'install'              => ['install', PreInstallEvent::class, InstallEvent::class];
        yield 'uninstall'            => ['uninstall', PreUninstallEvent::class, UninstallEvent::class];
        yield 'upgrade'              => ['upgrade', PreUpgradeEvent::class, UpgradeEvent::class];
        yield 'billing_install'      => ['billing_install', PreBillingInstallEvent::class, BillingInstallEvent::class];
        yield 'billing_subscription' => ['billing_subscription', PreBillingSubscriptionEvent::class, BillingSubscriptionEvent::class];
    }

    #[DataProvider('actions')]
    public function testDispatchesPreAndMainEventForEveryAppstoreAction(string $action, string $pre, string $main): void
    {
        $request  = self::sign(['action' => $action, 'shop' => 'shop-1', 'shop_url' => 'https://s', 'timestamp' => '1', 'application_version' => '2']);
        $response = $this->controller()->init($request);

        self::assertSame(200, $response->getStatusCode());
        self::assertCount(2, $this->dispatched);
        self::assertSame($pre::NAME, $this->dispatched[0]['name']);
        self::assertInstanceOf($pre, $this->dispatched[0]['event']);
        self::assertSame($main::NAME, $this->dispatched[1]['name']);
        self::assertInstanceOf($main, $this->dispatched[1]['event']);
        self::assertSame($request, $this->dispatched[1]['event']->getPayload(), 'payload is the full signed request');
    }

    public function testRejectsInvalidHashWith403AndDispatchesNothing(): void
    {
        $request = self::sign(['action' => 'install', 'shop' => 'shop-1'], 'other-secret');

        self::assertSame(403, $this->controller()->init($request)->getStatusCode());
        self::assertSame([], $this->dispatched);
    }

    public function testRejectsTamperedPayload(): void
    {
        $request         = self::sign(['action' => 'install', 'shop' => 'shop-1']);
        $request['shop'] = 'shop-2';

        self::assertSame(403, $this->controller()->init($request)->getStatusCode());
    }

    public function testReturns400WithoutAppstoreSecret(): void
    {
        self::assertSame(400, $this->controller(null)->init(['action' => 'install', 'hash' => 'x'])->getStatusCode());
        self::assertSame([], $this->dispatched);
    }

    public function testUnknownActionThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('does_not_exist');

        $this->controller()->init(self::sign(['action' => 'does_not_exist', 'shop' => 'shop-1']));
    }
}
