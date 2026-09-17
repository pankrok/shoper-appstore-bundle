<?php

namespace PanKrok\ShoperAppstoreBundle\Tests\Model;

use PanKrok\ShoperAppstoreBundle\Controller\API\Client\BearerInterface;
use PanKrok\ShoperAppstoreBundle\Model\Resource\CollectionProduct;
use PanKrok\ShoperAppstoreBundle\Model\Resource\PaymentChannel;
use PanKrok\ShoperAppstoreBundle\Model\Resource\Product;
use PanKrok\ShoperAppstoreBundle\Model\ResponseModel;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class PaginationAndSubResourceTest extends TestCase
{
    /** @var array<int, array> */
    private array $sent = [];

    /**
     * @param callable(array): string $bodyFor returns the JSON body for a given request
     */
    private function client(callable $bodyFor): BearerInterface
    {
        $sent = &$this->sent;

        return new class($sent, $bodyFor) implements BearerInterface {
            public function __construct(private array &$sent, private $bodyFor) {}
            public function setHttpClient(HttpClientInterface $client): void {}
            public function getHttpClient(): HttpClientInterface { throw new \LogicException(); }
            public function setHttpClientOptions(array $options): void {}
            public function request($request, $bulk = false): ResponseModel
            {
                $this->sent[] = $request;
                return new ResponseModel(200, [], ($this->bodyFor)($request));
            }
            public function getResponse(): ResponseInterface { throw new \LogicException(); }
            public function setToken(string $token): void {}
            public function getToken(): ?string { return 'token'; }
            public function setOnTokenRefreshed(?callable $callback): void {}
            public function getRateLimitState(): array { return ['calls' => null, 'limit' => null, 'bandwidth' => null]; }
        };
    }

    private static function page(int $page, int $pages, array $ids): string
    {
        return json_encode([
            'count' => 5,
            'pages' => $pages,
            'page'  => $page,
            'list'  => array_map(fn(int $id) => ['product_id' => $id], $ids),
        ]);
    }

    public function testResponseModelExposesCollectionMetadata(): void
    {
        $response = new ResponseModel(200, [], self::page(2, 3, [3, 4]));

        self::assertTrue($response->isCollection());
        self::assertSame(5, $response->getCount());
        self::assertSame(3, $response->getPages());
        self::assertSame(2, $response->getPage());
        self::assertSame([['product_id' => 3], ['product_id' => 4]], $response->getList());
    }

    public function testResponseModelSingleObjectIsNotCollection(): void
    {
        $response = new ResponseModel(200, [], '{"product_id":1,"code":"X"}');

        self::assertFalse($response->isCollection());
        self::assertNull($response->getPages());
        self::assertSame([], $response->getList());
    }

    public function testIterateWalksAllPagesAndRestoresState(): void
    {
        $client = $this->client(fn(array $r) => match ($r['options']['query']['page']) {
            1 => self::page(1, 3, [1, 2]),
            2 => self::page(2, 3, [3, 4]),
            3 => self::page(3, 3, [5]),
        });

        $product = (new Product($client))->setLimit(2)->setFilters(['active' => 1]);
        $ids     = array_column(iterator_to_array($product->iterate(), false), 'product_id');

        self::assertSame([1, 2, 3, 4, 5], $ids);
        self::assertCount(3, $this->sent);
        self::assertSame('{"active":1}', $this->sent[2]['options']['query']['filters'], 'filters kept on every page');
        self::assertSame(1, $product->getPage(), 'page restored after iteration');
    }

    public function testIterateStartsFromCurrentPage(): void
    {
        $client = $this->client(fn(array $r) => match ($r['options']['query']['page']) {
            2 => self::page(2, 3, [3, 4]),
            3 => self::page(3, 3, [5]),
        });

        $ids = array_column(iterator_to_array((new Product($client))->setPage(2)->iterate(), false), 'product_id');

        self::assertSame([3, 4, 5], $ids);
    }

    public function testIterateHandlesEmptyCollection(): void
    {
        $client = $this->client(fn() => json_encode(['count' => 0, 'pages' => 0, 'page' => 1, 'list' => []]));

        self::assertSame([], iterator_to_array((new Product($client))->iterate(), false));
        self::assertCount(1, $this->sent);
    }

    public function testIterateRejectsBulkMode(): void
    {
        $this->expectException(\LogicException::class);
        (new Product($this->client(fn() => '{}'), true))->iterate()->current();
    }

    public function testOffsetReplacesPage(): void
    {
        (new Product($this->client(fn() => '{}')))->setOffset(40)->setLimit(20)->get();

        $query = $this->sent[0]['options']['query'];
        self::assertSame(40, $query['offset']);
        self::assertArrayNotHasKey('page', $query);
    }

    public function testNegativeOffsetIsRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        (new Product($this->client(fn() => '{}')))->setOffset(-1);
    }

    public function testSubResourceBuildsParentPath(): void
    {
        $client = $this->client(fn() => '{}');

        (new CollectionProduct($client))->setParent(3)->get();
        (new CollectionProduct($client))->setParent(3)->put(15, ['position' => 1]);
        (new PaymentChannel($client))->setParent(2)->get(7);

        self::assertSame('collections/3/products', $this->sent[0]['url']);
        self::assertSame('collections/3/products/15', $this->sent[1]['url']);
        self::assertSame('payments/2/channels/7', $this->sent[2]['url']);
    }

    public function testSubResourceWithoutParentThrows(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('setParent');
        (new CollectionProduct($this->client(fn() => '{}')))->get();
    }

    public function testSubResourceBulkPathIncludesParent(): void
    {
        $call = (new CollectionProduct($this->client(fn() => '{}'), true))->setParent(3)->put(15, ['position' => 1]);

        self::assertSame('/webapi/rest/collections/3/products/15', $call['path']);
    }
}
