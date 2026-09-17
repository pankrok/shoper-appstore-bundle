<?php

namespace PanKrok\ShoperAppstoreBundle\Tests\Controller;

use Doctrine\ORM\EntityManagerInterface;
use PanKrok\ShoperAppstoreBundle\Controller\API\Client;
use PanKrok\ShoperAppstoreBundle\Controller\API\Client\BasicAuth;
use PanKrok\ShoperAppstoreBundle\Controller\API\Client\BearerInterface;
use PanKrok\ShoperAppstoreBundle\Controller\API\Client\OAuth;
use PanKrok\ShoperAppstoreBundle\Controller\ApiController;
use PanKrok\ShoperAppstoreBundle\Entity\AccessTokens;
use PanKrok\ShoperAppstoreBundle\Entity\Shops;
use PanKrok\ShoperAppstoreBundle\Model\BulkModel;
use PanKrok\ShoperAppstoreBundle\Model\Resource\Product;
use PanKrok\ShoperAppstoreBundle\Repository\ShopsRepository;
use PHPUnit\Framework\Attributes\IgnoreDeprecations;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class ApiControllerTest extends TestCase
{
    private const SECRET  = 'appstore-secret';
    private const OPTIONS = ['appId' => 'app', 'appSecret' => 'sec', 'appstoreSecret' => self::SECRET, 'rateLimit' => ['maxRetries' => 1, 'throttle' => false]];

    private ShopsRepository&MockObject $shops;
    private EntityManagerInterface&MockObject $em;
    /** @var array<int, array{method: string, url: string}> */
    private array $http = [];
    /** @var MockResponse[] */
    private array $responses = [];

    protected function setUp(): void
    {
        $this->shops = $this->createMock(ShopsRepository::class);
        $this->em    = $this->createMock(EntityManagerInterface::class);
    }

    /**
     * ApiController with createClient() swapped for adapters backed by MockHttpClient.
     */
    private function controller(array $options = self::OPTIONS): ApiController
    {
        $http      = &$this->http;
        $responses = &$this->responses;

        return new class(new ParameterBag(['appstore' => $options]), $this->shops, $this->em, $http, $responses) extends ApiController {
            public function __construct($bag, $shops, $em, private array &$http, private array &$responses)
            {
                parent::__construct($bag, $shops, $em);
            }

            protected function createClient(string $adapter): BearerInterface
            {
                $client = Client::factory($adapter, ['options' => $this->apiOptions, 'entrypoint' => $this->shopUrl]);
                $client->setHttpClient(new MockHttpClient(function (string $method, string $url) {
                    $this->http[] = ['method' => $method, 'url' => $url];
                    return array_shift($this->responses) ?? new MockResponse('{}');
                }));

                return $client;
            }
        };
    }

    private function shop(int $expiresIn): Shops
    {
        $token = (new AccessTokens())
            ->setAccessToken('access')
            ->setRefreshToken('refresh')
            ->setExpiresAt(new \DateTimeImmutable('@' . (time() + $expiresIn)))
            ->setCreatedAt(new \DateTimeImmutable());

        return (new Shops())->setShop('shop-1')->setShopUrl('https://shop-1.example')->setVersion('1')->setAccessTokens($token);
    }

    private static function signedParams(string $shop, string $secret = self::SECRET): array
    {
        $params = ['place' => 'dashboard', 'shop' => $shop, 'timestamp' => '1700000000'];
        ksort($params);
        $pairs = [];
        foreach ($params as $k => $v) {
            $pairs[] = "$k=$v";
        }
        $params['hash'] = hash_hmac('sha512', implode('&', $pairs), $secret);

        return $params;
    }

    // --- initFromRequest -------------------------------------------------------

    public function testInitFromRequestWithValidHashBindsShopAndToken(): void
    {
        $shop = $this->shop(90 * 86400);
        $this->shops->method('findOneBy')->with(['shop' => 'shop-1'])->willReturn($shop);
        $this->em->expects(self::never())->method('flush');

        $api = $this->controller();
        $api->initFromRequest(self::signedParams('shop-1'));

        self::assertSame($shop, $api->getActiveShop());
        self::assertSame('https://shop-1.example', $api->getShopUrl());
        self::assertInstanceOf(OAuth::class, $api->getHttpClient());
        self::assertSame('access', $api->getHttpClient()->getToken());
        self::assertSame([], $this->http, 'token far from expiry: no refresh call');
    }

    public function testInitFromRequestRejectsTamperedHash(): void
    {
        $this->shops->expects(self::never())->method('findOneBy');

        $this->expectExceptionMessage('Invalid hash');
        $this->controller()->initFromRequest(self::signedParams('shop-1', 'wrong-secret'));
    }

    public function testInitFromRequestSkipsHashWhenAsked(): void
    {
        $this->shops->method('findOneBy')->willReturn($this->shop(90 * 86400));

        $api = $this->controller();
        $api->initFromRequest(['shop' => 'shop-1'], false);

        self::assertNotNull($api->getActiveShop());
        self::assertSame(['shop' => 'shop-1'], $api->getRequestParams());
    }

    public function testInitFromRequestIgnoresParamsWithoutShop(): void
    {
        $this->shops->expects(self::never())->method('findOneBy');

        $api = $this->controller();
        $api->initFromRequest(['foo' => 'bar']);

        self::assertNull($api->getActiveShop());
    }

    public function testInitFromRequestRefreshesTokenExpiringWithin24h(): void
    {
        $shop  = $this->shop(3600);
        $token = $shop->getAccessTokens();
        $this->shops->method('findOneBy')->willReturn($shop);
        $this->em->expects(self::once())->method('flush');
        $this->responses = [new MockResponse('{"access_token":"new-access","refresh_token":"new-refresh","expires_in":7200}')];

        $api = $this->controller();
        $api->initFromRequest(['shop' => 'shop-1'], false);

        self::assertCount(1, $this->http);
        self::assertStringContainsString('grant_type=refresh_token', $this->http[0]['url']);
        self::assertSame('new-access', $token->getAccessToken());
        self::assertSame('new-refresh', $token->getRefreshToken());
        self::assertGreaterThan(time() + 7000, $token->getExpiresAt()->getTimestamp());
        self::assertSame('new-access', $api->getHttpClient()->getToken());
    }

    public function testClientRefreshOn401PersistsTokenThroughController(): void
    {
        $shop  = $this->shop(90 * 86400);
        $token = $shop->getAccessTokens();
        $this->shops->method('findOneBy')->willReturn($shop);
        $this->em->expects(self::once())->method('flush');
        $this->responses = [
            new MockResponse('{"error":"unauthorized_client"}', ['http_code' => 401]),
            new MockResponse('{"access_token":"a2","refresh_token":"r2"}'),
            new MockResponse('{"product_id":1}'),
        ];

        $api = $this->controller();
        $api->initFromRequest(['shop' => 'shop-1'], false);
        $api->product->get(1);

        self::assertSame('a2', $token->getAccessToken(), 'callback wired by bindTokenToClient persisted the token');
        self::assertSame('r2', $token->getRefreshToken());
    }

    // --- refreshToken (cron) ---------------------------------------------------

    public function testRefreshTokenOnlyRefreshesWhenNeeded(): void
    {
        $api = $this->controller();
        $this->em->expects(self::never())->method('flush');

        $api->refreshToken($this->shop(90 * 86400));

        self::assertSame([], $this->http);
    }

    public function testRefreshTokenPropagatesApiErrors(): void
    {
        $this->responses = [new MockResponse('{"error":"invalid_grant"}', ['http_code' => 400])];
        $this->em->expects(self::never())->method('flush');

        $this->expectExceptionMessage('invalid_grant');
        $this->controller()->refreshToken($this->shop(60));
    }

    // --- Basic Auth --------------------------------------------------------------

    public function testUseBasicAuthMergesCredentialsOverConfig(): void
    {
        $api    = $this->controller();
        $client = $api->useBasicAuth('https://basic.example', ['username' => 'u', 'password' => 'p']);

        self::assertInstanceOf(BasicAuth::class, $client);
        self::assertSame($client, $api->getHttpClient());
        self::assertSame('https://basic.example', $api->getShopUrl());
        self::assertSame('u', $api->getOptions()['username']);
        self::assertSame(['maxRetries' => 1, 'throttle' => false], $api->getOptions()['rateLimit'], 'bundle config survives credential override');
    }

    public function testShopUrlFromConfigIsUsedWhenNotOverridden(): void
    {
        $api = $this->controller(['username' => 'u', 'password' => 'p', 'shopurl' => 'https://cfg.example']);

        self::assertSame('https://cfg.example', $api->getShopUrl());
        self::assertFalse($api->isOAuthMode());
        $api->useBasicAuth();
        self::assertSame('https://cfg.example', $api->getShopUrl());
    }

    // --- misc --------------------------------------------------------------------

    public function testResourceAndBulkAccessors(): void
    {
        $api = $this->controller();
        $api->useBasicAuth('https://x');

        self::assertInstanceOf(Product::class, $api->product);
        self::assertInstanceOf(BulkModel::class, $api->bulk);
        self::assertTrue($api->isOAuthMode());
    }

    public function testUnknownResourceThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Prodcut');
        $this->controller()->prodcut;
    }

    public function testBindShopSetsContextWithoutTouchingClient(): void
    {
        $api  = $this->controller();
        $shop = $this->shop(60);

        $api->bindShop($shop);

        self::assertSame($shop, $api->getActiveShop());
        self::assertSame('https://shop-1.example', $api->getShopUrl());
        self::assertSame([], $this->http);
    }

    #[IgnoreDeprecations]
    public function testDeprecatedAliasesForwardToCurrentApi(): void
    {
        $this->shops->method('findOneBy')->willReturn($this->shop(90 * 86400));
        $api = $this->controller();

        $api->setParams(['shop' => 'shop-1'], false);
        self::assertSame(['shop' => 'shop-1'], $api->getParams());
        self::assertSame($api->getActiveShop(), $api->getShop());
        self::assertTrue($api->getAppId());
        self::assertSame($api->getHttpClient(), $api->getClient());

        $client = $api->setBasicAuth('https://b', ['username' => 'u', 'password' => 'p']);
        self::assertInstanceOf(BasicAuth::class, $client);

        $other = $this->createMock(BearerInterface::class);
        $api->setClient($other);
        self::assertSame($other, $api->getHttpClient());

        $api->refreshShopToken($this->shop(90 * 86400));
        self::assertSame([], $this->http);
    }
}
