<?php

namespace PanKrok\ShoperAppstoreBundle\Tests\Model;

use PanKrok\ShoperAppstoreBundle\Controller\API\Client\BearerInterface;
use PanKrok\ShoperAppstoreBundle\Entity\AccessTokens;
use PanKrok\ShoperAppstoreBundle\Entity\Billings;
use PanKrok\ShoperAppstoreBundle\Entity\Shops;
use PanKrok\ShoperAppstoreBundle\Entity\Subscriptions;
use PanKrok\ShoperAppstoreBundle\Exception\ShoperApiException;
use PanKrok\ShoperAppstoreBundle\Model\BulkModel;
use PanKrok\ShoperAppstoreBundle\Model\Resource\Product;
use PanKrok\ShoperAppstoreBundle\Model\ResponseModel;
use PHPUnit\Framework\Attributes\IgnoreDeprecations;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * BulkModel guards, ordering/limit validation, ResponseModel decoding,
 * ShoperApiException parsing and entity relation bookkeeping.
 */
final class ModelEdgeCasesTest extends TestCase
{
    private array $sent = [];

    private function client(): BearerInterface
    {
        $sent = &$this->sent;

        return new class($sent) implements BearerInterface {
            public function __construct(private array &$sent) {}
            public function setHttpClient(HttpClientInterface $client): void {}
            public function getHttpClient(): HttpClientInterface { throw new \LogicException(); }
            public function setHttpClientOptions(array $options): void {}
            public function request($request, $bulk = false): ResponseModel { $this->sent[] = $request; return new ResponseModel(200, [], '[]'); }
            public function getResponse(): ResponseInterface { throw new \LogicException(); }
            public function setToken(string $token): void {}
            public function getToken(): ?string { return null; }
            public function setOnTokenRefreshed(?callable $callback): void {}
            public function getRateLimitState(): array { return ['calls' => null, 'limit' => null, 'bandwidth' => null]; }
        };
    }

    // --- BulkModel -----------------------------------------------------------------

    public function testBulkRequiresResourceBeforeCall(): void
    {
        $this->expectExceptionMessage('set resource first');
        (new BulkModel($this->client()))->get();
    }

    public function testBulkRejectsUnknownResource(): void
    {
        $this->expectExceptionMessage('does not exist');
        (new BulkModel($this->client()))->nope;
    }

    public function testBulkEnforces25CallLimit(): void
    {
        $bulk = new BulkModel($this->client());
        for ($i = 1; $i <= 25; ++$i) {
            $bulk->product->get($i);
        }

        $this->expectExceptionMessage('limit exceeded');
        $bulk->product->get(26);
    }

    public function testBulkForwardsConfigurationCallsToResourceAndSendsOnce(): void
    {
        $bulk = new BulkModel($this->client());
        $bulk->product->setLimit(50)->setFilters(['active' => 1])->get();
        $bulk->addId('my-list');

        $bulk->send();

        self::assertSame('bulk', $this->sent[0]['url']);
        self::assertSame('POST', $this->sent[0]['method']);
        $body = $this->sent[0]['options']['json'];
        self::assertCount(1, $body);
        self::assertSame('my-list', $body[0]['id']);
        self::assertSame(50, $body[0]['params']['limit']);
        self::assertSame('{"active":1}', $body[0]['params']['filters']);

        $bulk->send();
        self::assertSame([], $this->sent[1]['options']['json'], 'body is reset after send()');
    }

    // --- ordering / limit ----------------------------------------------------------

    public function testOrderExpressionsAreNormalised(): void
    {
        $p = new Product($this->client());

        self::assertSame(['name asc'], $p->setOrder('name')->getOrder());
        self::assertSame(['name asc'], $p->setOrder('+name')->getOrder());
        self::assertSame(['stock.price desc'], $p->setOrder('-stock.price')->getOrder());
        self::assertSame(['add_date DESC'], $p->setOrder('add_date DESC')->getOrder());
        self::assertSame([], (new Product($this->client()))->getOrder());
    }

    public function testInvalidOrderExpressionThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        (new Product($this->client()))->setOrder('!!!');
    }

    public function testLimitBounds(): void
    {
        $p = new Product($this->client());
        self::assertSame(50, $p->setLimit(50)->getLimit());
        self::assertSame(1, $p->setLimit(1)->getLimit());

        $this->expectException(\InvalidArgumentException::class);
        $p->setLimit(51);
    }

    public function testGettersReflectState(): void
    {
        $p = (new Product($this->client()))->setBody(['a' => 1])->setFilters(['x' => ['>' => 1]]);

        self::assertSame(['a' => 1], $p->getBody());
        self::assertSame(['x' => ['>' => 1]], $p->getFilters());
        self::assertSame([], (new Product($this->client()))->getFilters());
        self::assertSame(20, $p->getLimit());
        self::assertNull($p->getOffset());
        self::assertNull($p->getParent());
    }

    // --- ResponseModel -------------------------------------------------------------

    public function testResponseModelDecodesAndExposesRawParts(): void
    {
        $r = new ResponseModel(200, ['x-shop-api-calls' => ['3']], '{"a":1}');

        self::assertSame(200, $r->getStatusCode());
        self::assertSame(['x-shop-api-calls' => ['3']], $r->getHeaders());
        self::assertSame('{"a":1}', $r->getBody());
        self::assertSame(['a' => 1], $r->toArray());
        self::assertSame(['a' => 1], $r->getBodyArray());
        self::assertSame(['code' => 200, 'headers' => ['x-shop-api-calls' => ['3']], 'response' => '{"a":1}'], $r->getAll());
        self::assertSame([], (new ResponseModel(200, [], ''))->toArray());
        self::assertSame([], (new ResponseModel(200, [], 'null'))->toArray());
    }

    public function testResponseModelRejectsInvalidJson(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to decode');
        (new ResponseModel(200, [], '<html>'))->toArray();
    }

    #[IgnoreDeprecations]
    public function testResponseModelDeprecatedGetCode(): void
    {
        self::assertSame(418, (new ResponseModel(418, [], ''))->getCode());
    }

    // --- ShoperApiException --------------------------------------------------------

    public function testExceptionParsesShoperErrorBody(): void
    {
        $e = ShoperApiException::fromResponse(403, '{"error":"insufficient_scope","error_description":"no products_read"}');

        self::assertSame(403, $e->getStatusCode());
        self::assertSame('insufficient_scope', $e->getShoperError());
        self::assertSame('no products_read', $e->getShoperErrorDescription());
        self::assertSame('insufficient_scope: no products_read', $e->getMessage());
        self::assertSame(['error' => 'insufficient_scope', 'error_description' => 'no products_read', 'status_code' => 403], $e->toArray());
        self::assertStringContainsString('insufficient_scope', $e->getRawResponse());
    }

    public function testExceptionFallsBackForNonJsonBody(): void
    {
        $e = ShoperApiException::fromResponse(502, '<html>Bad Gateway</html>');

        self::assertSame('api_error', $e->getShoperError());
        self::assertSame('<html>Bad Gateway</html>', $e->getShoperErrorDescription());
        self::assertSame('api_error: <html>Bad Gateway</html>', $e->getMessage());
    }

    public function testExceptionWithoutDescriptionHasBareMessage(): void
    {
        self::assertSame('server_error', (new ShoperApiException(500, 'server_error'))->getMessage());
    }

    // --- entities ------------------------------------------------------------------

    public function testShopRelationsKeepBothSidesInSync(): void
    {
        $shop  = (new Shops())->setShop('s1')->setShopUrl('https://s1')->setVersion('1')->setInstalled(true)->setCreatedAt(new \DateTimeImmutable());
        $token = (new AccessTokens())->setAccessToken('a')->setRefreshToken('r');

        $shop->setAccessTokens($token);
        self::assertSame($shop, $token->getShop());
        self::assertSame('s1', (string) $shop);
        self::assertSame('', (string) $token, 'unsaved token has no id');

        $shop->setAccessTokens(null);
        self::assertNull($token->getShop());

        $billing = new Billings();
        $shop->addBilling($billing)->addBilling($billing);
        self::assertCount(1, $shop->getBillings());
        self::assertSame($shop, $billing->getShop());
        $shop->removeBilling($billing);
        self::assertCount(0, $shop->getBillings());
        self::assertNull($billing->getShop());

        $sub = (new Subscriptions())->setExpiresAt(new \DateTimeImmutable('2027-01-01'));
        $shop->addSubscription($sub);
        self::assertSame($shop, $sub->getShop());
        self::assertSame('2027-01-01', $sub->getExpiresAt()->format('Y-m-d'));
        $shop->removeSubscription($sub);
        self::assertNull($sub->getShop());
        self::assertTrue($shop->isInstalled());
        self::assertNull($shop->getId());
    }
}
