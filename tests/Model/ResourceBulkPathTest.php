<?php

namespace PanKrok\ShoperAppstoreBundle\Tests\Model;

use PanKrok\ShoperAppstoreBundle\Controller\API\Client\BearerInterface;
use PanKrok\ShoperAppstoreBundle\Model\BulkModel;
use PanKrok\ShoperAppstoreBundle\Model\Resource\Metafield;
use PanKrok\ShoperAppstoreBundle\Model\Resource\Product;
use PanKrok\ShoperAppstoreBundle\Model\ResponseModel;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * Regression tests for bulk request path building (resource ID was dropped
 * from PUT/DELETE/GET-by-id paths) and the 1-based page default.
 */
final class ResourceBulkPathTest extends TestCase
{
    /** @var array<int, array> every request handed to the client */
    private array $sent = [];

    private function client(): BearerInterface
    {
        $sent = &$this->sent;

        return new class($sent) implements BearerInterface {
            public function __construct(private array &$sent) {}
            public function setHttpClient(HttpClientInterface $client): void {}
            public function getHttpClient(): HttpClientInterface { throw new \LogicException(); }
            public function setHttpClientOptions(array $options): void {}
            public function request($request, $bulk = false): ResponseModel
            {
                $this->sent[] = $request;
                return new ResponseModel(200, [], '[]');
            }
            public function getResponse(): ResponseInterface { throw new \LogicException(); }
            public function setToken(string $token): void {}
            public function getToken(): ?string { return 'token'; }
            public function setOnTokenRefreshed(?callable $callback): void {}
            public function getRateLimitState(): array { return ['calls' => null, 'limit' => null, 'bandwidth' => null]; }
        };
    }

    public function testBulkPutKeepsResourceId(): void
    {
        $call = (new Product($this->client(), true))->put(15, ['code' => 'X']);

        self::assertSame('PUT', $call['method']);
        self::assertSame('/webapi/rest/products/15', $call['path']);
        self::assertSame(['code' => 'X'], $call['body']);
        self::assertArrayNotHasKey('params', $call, 'list params must not be sent on PUT');
    }

    public function testBulkDeleteKeepsResourceId(): void
    {
        $call = (new Product($this->client(), true))->delete(15);

        self::assertSame('DELETE', $call['method']);
        self::assertSame('/webapi/rest/products/15', $call['path']);
        self::assertArrayNotHasKey('body', $call);
    }

    public function testBulkGetByIdKeepsResourceId(): void
    {
        $call = (new Product($this->client(), true))->get(15);

        self::assertSame('/webapi/rest/products/15', $call['path']);
    }

    public function testBulkGetListSendsParamsAndNoBody(): void
    {
        $call = (new Product($this->client(), true))
            ->setLimit(50)
            ->setPage(3)
            ->setFilters(['product_id' => ['IN' => [1, 2]]])
            ->get();

        self::assertSame('/webapi/rest/products', $call['path']);
        self::assertSame(3, $call['params']['page']);
        self::assertSame(50, $call['params']['limit']);
        self::assertSame('{"product_id":{"IN":[1,2]}}', $call['params']['filters']);
        self::assertArrayNotHasKey('body', $call);
    }

    public function testBulkPostSendsBodyAndNoParams(): void
    {
        $call = (new Product($this->client(), true))->post(['code' => 'NEW']);

        self::assertSame('POST', $call['method']);
        self::assertSame('/webapi/rest/products', $call['path']);
        self::assertSame(['code' => 'NEW'], $call['body']);
        self::assertArrayNotHasKey('params', $call);
    }

    public function testBulkMetafieldUsesObjectPath(): void
    {
        $resource = (new Metafield($this->client(), true))->setObject('Product');

        self::assertSame('/webapi/rest/metafields/Product', $resource->get()['path']);
        self::assertSame('/webapi/rest/metafields/Product/7', $resource->get(7)['path']);
        self::assertSame('/webapi/rest/metafields/Product', $resource->post(['namespace' => 'x'])['path']);
    }

    public function testMetafieldGetDoesNotMutateResourceUrl(): void
    {
        $resource = (new Metafield($this->client()))->setObject('Product');
        $resource->get();
        $resource->get();

        self::assertSame('metafields/Product', $this->sent[1]['url']);
    }

    public function testBulkModelAssignsSequentialIdsAndKeepsPath(): void
    {
        $bulk = new BulkModel($this->client());
        $bulk->product->put(1, ['a' => 1]);
        $bulk->product->delete(2);
        $bulk->send();

        $body = $this->sent[0]['options']['json'];
        self::assertSame(0, $body[0]['id']);
        self::assertSame('/webapi/rest/products/1', $body[0]['path']);
        self::assertSame(1, $body[1]['id']);
        self::assertSame('/webapi/rest/products/2', $body[1]['path']);
    }

    public function testDefaultPageIsOne(): void
    {
        (new Product($this->client()))->get();

        self::assertSame(1, $this->sent[0]['options']['query']['page']);
    }

    public function testPageZeroIsRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        (new Product($this->client()))->setPage(0);
    }
}
