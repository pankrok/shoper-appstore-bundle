<?php

namespace PanKrok\ShoperAppstoreBundle\Tests\EventSubscriber;

use Doctrine\ORM\EntityManagerInterface;
use PanKrok\ShoperAppstoreBundle\Controller\API\Client\OAuth;
use PanKrok\ShoperAppstoreBundle\Entity\AccessTokens;
use PanKrok\ShoperAppstoreBundle\Entity\Billings;
use PanKrok\ShoperAppstoreBundle\Entity\Shops;
use PanKrok\ShoperAppstoreBundle\Entity\Subscriptions;
use PanKrok\ShoperAppstoreBundle\Events\BillingInstallEvent;
use PanKrok\ShoperAppstoreBundle\Events\BillingSubscriptionEvent;
use PanKrok\ShoperAppstoreBundle\Events\InstallEvent;
use PanKrok\ShoperAppstoreBundle\Events\PostBillingInstallEvent;
use PanKrok\ShoperAppstoreBundle\Events\PostInstallEvent;
use PanKrok\ShoperAppstoreBundle\Events\UninstallEvent;
use PanKrok\ShoperAppstoreBundle\Events\UpgradeEvent;
use PanKrok\ShoperAppstoreBundle\EventSubscriber\BillingInstallSubscriber;
use PanKrok\ShoperAppstoreBundle\EventSubscriber\BillingSubscriptionSubscriber;
use PanKrok\ShoperAppstoreBundle\EventSubscriber\InstallSubscriber;
use PanKrok\ShoperAppstoreBundle\EventSubscriber\UninstallSubscriber;
use PanKrok\ShoperAppstoreBundle\EventSubscriber\UpgradeSubscriber;
use PanKrok\ShoperAppstoreBundle\Exception\ShoperApiException;
use PanKrok\ShoperAppstoreBundle\Repository\ShopsRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class LifecycleSubscribersTest extends TestCase
{
    private const PAYLOAD = [
        'action' => 'install', 'shop' => 'shop-1', 'shop_url' => 'https://shop-1.example',
        'application_version' => '3', 'auth_code' => 'code-123', 'subscription_end_time' => '2027-01-01 00:00:00',
    ];

    private ShopsRepository&MockObject $shops;
    private EntityManagerInterface&MockObject $em;
    private EventDispatcher $dispatcher;
    /** @var object[] */
    private array $persisted = [];
    /** @var object[] */
    private array $removed = [];
    /** @var array<int, object> */
    private array $dispatched = [];

    protected function setUp(): void
    {
        $this->shops = $this->createMock(ShopsRepository::class);
        $this->em    = $this->createMock(EntityManagerInterface::class);
        $this->em->method('persist')->willReturnCallback(function (object $e): void { $this->persisted[] = $e; });
        $this->em->method('remove')->willReturnCallback(function (object $e): void { $this->removed[] = $e; });
        $this->dispatcher = new EventDispatcher();
        foreach ([PostInstallEvent::NAME, PostBillingInstallEvent::NAME] as $name) {
            $this->dispatcher->addListener($name, function (object $e): void { $this->dispatched[] = $e; });
        }
    }

    private function existingShop(bool $withToken = true): Shops
    {
        $shop = (new Shops())->setShop('shop-1')->setShopUrl('https://old.example')->setVersion('1')->setInstalled(false);
        if ($withToken) {
            $shop->setAccessTokens((new AccessTokens())->setAccessToken('old')->setRefreshToken('old-r'));
        }

        return $shop;
    }

    private function installSubscriber(string $tokenJson, int $status = 200): InstallSubscriber
    {
        $http = new MockHttpClient(new MockResponse($tokenJson, ['http_code' => $status]));

        return new class($this->dispatcher, $this->em, new ParameterBag(['appstore' => ['appId' => 'a', 'appSecret' => 's']]), $this->shops, $http) extends InstallSubscriber {
            public function __construct($d, $em, $bag, $shops, private MockHttpClient $http)
            {
                parent::__construct($d, $em, $bag, $shops);
            }

            protected function createClient(array $options): OAuth
            {
                $client = new OAuth($options);
                $client->setHttpClient($this->http);
                return $client;
            }
        };
    }

    // --- install -----------------------------------------------------------------

    public function testInstallCreatesShopAndTokenForNewShop(): void
    {
        $this->shops->method('findOneBy')->willReturn(null);
        $this->em->expects(self::once())->method('flush');

        $this->installSubscriber('{"access_token":"acc","refresh_token":"ref","expires_in":100}')
            ->onInstallAction(new InstallEvent(self::PAYLOAD));

        [$shop, $token] = $this->persisted;
        self::assertInstanceOf(Shops::class, $shop);
        self::assertSame('shop-1', $shop->getShop());
        self::assertSame('https://shop-1.example', $shop->getShopUrl());
        self::assertSame('3', $shop->getVersion());
        self::assertTrue($shop->isInstalled());
        self::assertInstanceOf(AccessTokens::class, $token);
        self::assertSame('acc', $token->getAccessToken());
        self::assertSame('ref', $token->getRefreshToken());
        self::assertSame($shop, $token->getShop());
        self::assertEqualsWithDelta(time() + 100, $token->getExpiresAt()->getTimestamp(), 2);

        self::assertCount(1, $this->dispatched);
        self::assertInstanceOf(PostInstallEvent::class, $this->dispatched[0]);
        self::assertSame($shop, $this->dispatched[0]->getShop());
    }

    public function testInstallReplacesTokenOnReinstall(): void
    {
        $shop     = $this->existingShop();
        $oldToken = $shop->getAccessTokens();
        $this->shops->method('findOneBy')->willReturn($shop);

        $this->installSubscriber('{"access_token":"acc","refresh_token":"ref"}')
            ->onInstallAction(new InstallEvent(self::PAYLOAD));

        self::assertSame([$oldToken], $this->removed);
        self::assertTrue($shop->isInstalled());
        self::assertSame('https://shop-1.example', $shop->getShopUrl(), 'url refreshed from payload');
        self::assertSame('3', $shop->getVersion());
        self::assertSame($shop, $this->persisted[0]);
        self::assertSame('acc', $this->persisted[1]->getAccessToken());
        self::assertEqualsWithDelta(time() + OAuth::DEFAULT_EXPIRES_IN, $this->persisted[1]->getExpiresAt()->getTimestamp(), 2, 'missing expires_in → 90 days');
    }

    public function testInstallFailsWhenAuthCodeExchangeFails(): void
    {
        $this->em->expects(self::never())->method('flush');

        $this->expectExceptionMessage('invalid_grant');
        $this->installSubscriber('{"error":"invalid_grant"}', 400)->onInstallAction(new InstallEvent(self::PAYLOAD));
    }

    public function testInstallFailsWithoutAccessTokenInResponse(): void
    {
        $this->expectException(ShoperApiException::class);
        $this->expectExceptionMessage('no access_token');
        $this->installSubscriber('{"unexpected":1}')->onInstallAction(new InstallEvent(self::PAYLOAD));
    }

    // --- uninstall ---------------------------------------------------------------

    public function testUninstallRemovesTokenAndFlagsShop(): void
    {
        $shop  = $this->existingShop()->setInstalled(true);
        $token = $shop->getAccessTokens();
        $this->shops->method('findOneBy')->with(['shop' => 'shop-1'])->willReturn($shop);
        $this->em->expects(self::once())->method('flush');

        (new UninstallSubscriber($this->em, $this->shops))->onUninstallAction(new UninstallEvent(self::PAYLOAD));

        self::assertSame([$token], $this->removed);
        self::assertFalse($shop->isInstalled());
        self::assertSame([$shop], $this->persisted);
    }

    public function testUninstallWithoutTokenStillFlagsShop(): void
    {
        $shop = $this->existingShop(false)->setInstalled(true);
        $this->shops->method('findOneBy')->willReturn($shop);

        (new UninstallSubscriber($this->em, $this->shops))->onUninstallAction(new UninstallEvent(self::PAYLOAD));

        self::assertSame([], $this->removed);
        self::assertFalse($shop->isInstalled());
    }

    public function testUninstallUnknownShopThrows(): void
    {
        $this->shops->method('findOneBy')->willReturn(null);

        $this->expectExceptionMessage("Can't find shop-1 shop");
        (new UninstallSubscriber($this->em, $this->shops))->onUninstallAction(new UninstallEvent(self::PAYLOAD));
    }

    // --- upgrade -----------------------------------------------------------------

    public function testUpgradeUpdatesVersionAndUrl(): void
    {
        $shop = $this->existingShop();
        $this->shops->method('findOneBy')->willReturn($shop);
        $this->em->expects(self::once())->method('flush');

        (new UpgradeSubscriber($this->em, $this->shops))->onUpgradeAction(new UpgradeEvent(self::PAYLOAD));

        self::assertSame('3', $shop->getVersion());
        self::assertSame('https://shop-1.example', $shop->getShopUrl());
    }

    public function testUpgradeUnknownShopThrows(): void
    {
        $this->shops->method('findOneBy')->willReturn(null);

        $this->expectExceptionMessage('shop not found');
        (new UpgradeSubscriber($this->em, $this->shops))->onUpgradeAction(new UpgradeEvent(self::PAYLOAD));
    }

    // --- billing -----------------------------------------------------------------

    public function testBillingInstallPersistsBillingAndDispatchesPostEvent(): void
    {
        $shop = $this->existingShop();
        $this->shops->method('findOneBy')->willReturn($shop);
        $this->em->expects(self::once())->method('flush');

        (new BillingInstallSubscriber($this->dispatcher, $this->em, $this->shops))
            ->onBillingInstall(new BillingInstallEvent(self::PAYLOAD));

        self::assertInstanceOf(Billings::class, $this->persisted[0]);
        self::assertSame($shop, $this->persisted[0]->getShop());
        self::assertNotNull($this->persisted[0]->getCreatedAt());
        self::assertInstanceOf(PostBillingInstallEvent::class, $this->dispatched[0]);
        self::assertSame($shop, $this->dispatched[0]->getShop());
    }

    public function testBillingInstallUnknownShopThrows(): void
    {
        $this->shops->method('findOneBy')->willReturn(null);

        $this->expectExceptionMessage("Can't find shop-1 shop");
        (new BillingInstallSubscriber($this->dispatcher, $this->em, $this->shops))
            ->onBillingInstall(new BillingInstallEvent(self::PAYLOAD));
    }

    public function testBillingSubscriptionPersistsExpiry(): void
    {
        $shop = $this->existingShop();
        $this->shops->method('findOneBy')->willReturn($shop);
        $this->em->expects(self::once())->method('flush');

        (new BillingSubscriptionSubscriber($this->em, $this->shops))
            ->onBillingSubscription(new BillingSubscriptionEvent(self::PAYLOAD));

        self::assertInstanceOf(Subscriptions::class, $this->persisted[0]);
        self::assertSame($shop, $this->persisted[0]->getShop());
        self::assertSame('2027-01-01 00:00:00', $this->persisted[0]->getExpiresAt()->format('Y-m-d H:i:s'));
    }

    public function testBillingSubscriptionUnknownShopThrows(): void
    {
        $this->shops->method('findOneBy')->willReturn(null);

        $this->expectExceptionMessage("Can't find shop-1 shop");
        (new BillingSubscriptionSubscriber($this->em, $this->shops))
            ->onBillingSubscription(new BillingSubscriptionEvent(self::PAYLOAD));
    }

    public function testSubscribedEventsMapToLifecycleNames(): void
    {
        self::assertSame([InstallEvent::NAME => 'onInstallAction'], InstallSubscriber::getSubscribedEvents());
        self::assertSame([UninstallEvent::NAME => 'onUninstallAction'], UninstallSubscriber::getSubscribedEvents());
        self::assertSame([UpgradeEvent::NAME => 'onUpgradeAction'], UpgradeSubscriber::getSubscribedEvents());
        self::assertSame([BillingInstallEvent::NAME => 'onBillingInstall'], BillingInstallSubscriber::getSubscribedEvents());
        self::assertSame([BillingSubscriptionEvent::NAME => 'onBillingSubscription'], BillingSubscriptionSubscriber::getSubscribedEvents());
    }
}
