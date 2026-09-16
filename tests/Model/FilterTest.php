<?php

namespace PanKrok\ShoperAppstoreBundle\Tests\Model;

use PanKrok\ShoperAppstoreBundle\Controller\API\Client\BearerInterface;
use PanKrok\ShoperAppstoreBundle\Model\Filter;
use PanKrok\ShoperAppstoreBundle\Model\Resource\AdditionalField;
use PanKrok\ShoperAppstoreBundle\Model\Resource\Metafield;
use PanKrok\ShoperAppstoreBundle\Model\Resource\Order;
use PanKrok\ShoperAppstoreBundle\Model\Resource\Product;
use PanKrok\ShoperAppstoreBundle\Model\Resource\Status;
use PanKrok\ShoperAppstoreBundle\Model\ResponseModel;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class FilterTest extends TestCase
{
    public function testLoneEqualityIsEmittedAsScalarShortForm(): void
    {
        self::assertSame(
            ['translations.pl_PL.active' => true, 'producer_id' => 7],
            Filter::create()->eq('translations.pl_PL.active', true)->eq('producer_id', 7)->toArray()
        );
    }

    public function testOperatorsProduceDocumentedSyntax(): void
    {
        $filter = Filter::create()
            ->like('translations.pl_PL.name', 'z%')
            ->notLike('code', 'OLD-%')
            ->in('category_id', [3, 4])
            ->notIn('product_id', [1])
            ->neq('type', Product::TYPE_BUNDLE)
            ->gt('stock.stock', 0);

        self::assertSame([
            'translations.pl_PL.name' => ['LIKE' => 'z%'],
            'code'        => ['NOT LIKE' => 'OLD-%'],
            'category_id' => ['IN' => [3, 4]],
            'product_id'  => ['NOT IN' => [1]],
            'type'        => ['!=' => 1],
            'stock.stock' => ['>' => 0],
        ], $filter->toArray());
    }

    public function testMultipleConditionsOnOneFieldAreMerged(): void
    {
        self::assertSame(
            ['stock.price' => ['>=' => 10, '<=' => 20]],
            Filter::create()->between('stock.price', 10, 20)->toArray()
        );

        self::assertSame(
            ['x' => ['=' => 1, '>' => 0]],
            Filter::create()->eq('x', 1)->gt('x', 0)->toArray(),
            'equality keeps its explicit operator once another condition joins'
        );
    }

    public function testTildeAliasesAndCaseInsensitiveOperators(): void
    {
        self::assertSame(
            ['a' => ['LIKE' => 'x%'], 'b' => ['NOT LIKE' => 'y%'], 'c' => ['IN' => [1]]],
            Filter::create()->where('a', '~', 'x%')->where('b', '!~', 'y%')->where('c', 'in', [1])->toArray()
        );
    }

    public function testUnknownOperatorIsRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Filter::create()->where('a', '<>', 1);
    }

    public function testInRequiresArray(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Filter::create()->where('a', 'IN', 1);
    }

    public function testFromArrayRoundTrips(): void
    {
        $shape = ['a' => 1, 'b' => ['>' => 2, 'LIKE' => 'x%'], 'c' => ['IN' => [1, 2]]];

        self::assertSame($shape, Filter::fromArray($shape)->toArray());
        self::assertTrue(Filter::create()->isEmpty());
    }

    public function testResourceAcceptsFilterInstance(): void
    {
        $sent = [];
        $client = new class($sent) implements BearerInterface {
            public function __construct(private array &$sent) {}
            public function setHttpClient(HttpClientInterface $client): void {}
            public function getHttpClient(): HttpClientInterface { throw new \LogicException(); }
            public function setHttpClientOptions(array $options): void {}
            public function request($request, $bulk = false): ResponseModel { $this->sent[] = $request; return new ResponseModel(200, [], '{}'); }
            public function getResponse(): ResponseInterface { throw new \LogicException(); }
            public function setToken(string $token): void {}
            public function getToken(): ?string { return null; }
            public function setOnTokenRefreshed(?callable $callback): void {}
            public function getRateLimitState(): array { return ['calls' => null, 'limit' => null, 'bandwidth' => null]; }
        };

        $product = (new Product($client))->setFilters(Filter::create()->eq('active', 1)->in('category_id', [3]));
        $product->get();

        self::assertSame('{"active":1,"category_id":{"IN":[3]}}', $sent[0]['options']['query']['filters']);
        self::assertSame(['active' => 1, 'category_id' => ['IN' => [3]]], $product->getFilters());
    }

    public function testDomainConstantsMatchDocs(): void
    {
        self::assertSame(0, Product::TYPE_PRODUCT);
        self::assertSame(1, Product::TYPE_BUNDLE);
        self::assertSame(Status::TYPE_CLOSED, Order::STATUS_TYPE_CLOSED);
        self::assertSame(4, Order::STATUS_TYPE_NOT_COMPLETED);
        self::assertSame(3, Order::ORIGIN_ALLEGRO);
        self::assertSame(4, Order::ORIGIN_WEBAPI);
        self::assertSame(128, AdditionalField::LOCATE_CONTACT_FORM);
        self::assertSame(
            AdditionalField::LOCATE_ORDER_FORM | AdditionalField::LOCATE_CONTACT_FORM,
            136,
            'locate is a bitmask'
        );
    }

    public function testMetafieldObjectNames(): void
    {
        self::assertSame('Product', Metafield::OBJECT_PRODUCT);
        self::assertSame('ProductGfx', Metafield::OBJECT_PRODUCT_GFX);
        self::assertSame('PaymentMethod', Metafield::OBJECT_PAYMENT_METHOD);
        self::assertSame('system', Metafield::OBJECT_SYSTEM);
        self::assertCount(43, Metafield::OBJECTS);
        self::assertContains(Metafield::OBJECT_USER_ADDRESS, Metafield::OBJECTS);
    }
}
