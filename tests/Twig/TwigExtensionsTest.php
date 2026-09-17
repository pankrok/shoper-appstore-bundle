<?php

namespace PanKrok\ShoperAppstoreBundle\Tests\Twig;

use PanKrok\ShoperAppstoreBundle\Controller\ApiController;
use PanKrok\ShoperAppstoreBundle\Controller\Twig\Extension\PathFunction;
use PanKrok\ShoperAppstoreBundle\Controller\Twig\Extension\ProductFilters;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

final class TwigExtensionsTest extends TestCase
{
    // --- ProductFilters ----------------------------------------------------------

    private function filters(): ProductFilters
    {
        $api = $this->createMock(ApiController::class);
        $api->method('getShopUrl')->willReturn('https://shop.example');

        return new ProductFilters($api);
    }

    public function testProductFiltersBuildShopUrls(): void
    {
        $f = $this->filters();

        self::assertSame('https://shop.example/userdata/public/gfx/abc.jpg', $f->productImg('abc'));
        self::assertSame('https://shop.example/admin/products/edit/id/42', $f->productAdminUrl(42));
        self::assertSame('https://shop.example/pl_PL/p/Red-Shoes/42?preview=true', $f->productFrontUrl(42, 'Red Shoes'));
        self::assertSame('https://shop.example/pl_PL/p/Red-Shoes/42', $f->productFrontUrl(42, 'Red Shoes', false));
        self::assertSame('https://shop.example/pl_PL/p/AB-C/1', $f->productFrontUrl(1, 'A/B  C', false), 'slashes removed, double space collapsed');
    }

    public function testProductFiltersAreRegisteredInTwig(): void
    {
        $twig = new Environment(new ArrayLoader([
            't' => "{{ 'img1'|productImg }} {{ 7|productAdminUrl }} {{ 7|productFrontUrl('X Y', false) }}",
        ]));
        $twig->addExtension($this->filters());

        self::assertSame(
            'https://shop.example/userdata/public/gfx/img1.jpg https://shop.example/admin/products/edit/id/7 https://shop.example/pl_PL/p/X-Y/7',
            $twig->render('t')
        );
    }

    // --- PathFunction --------------------------------------------------------------

    private function pathFunction(?Request $request): PathFunction
    {
        $stack = new RequestStack();
        if ($request) {
            $stack->push($request);
        }

        $generator = $this->createMock(UrlGeneratorInterface::class);
        $generator->method('generate')->willReturnCallback(
            fn(string $name, array $params, int $type) => '/' . $name . ($params ? '?' . http_build_query($params) : '') . ($type === UrlGeneratorInterface::RELATIVE_PATH ? '#rel' : '')
        );

        return new PathFunction($stack, $generator);
    }

    public function testPathAppendsIframeQueryStringToEveryLink(): void
    {
        $request = new Request(['shop' => 's1', 'hash' => 'h', 'place' => 'dashboard'], [], [], [], [], ['REQUEST_URI' => '/app?shop=s1']);

        self::assertSame('/orders?shop=s1&hash=h&place=dashboard', $this->pathFunction($request)->getPath('orders'));
    }

    public function testPathKeepsRouteParamsAndRelativeMode(): void
    {
        $request = new Request(['shop' => 's1'], [], [], [], [], ['REQUEST_URI' => '/app']);

        self::assertSame('/order?id=5#rel?shop=s1', $this->pathFunction($request)->getPath('order', ['id' => 5], true));
    }

    public function testPathIsPlainWithoutRequestOrInsideProfiler(): void
    {
        self::assertSame('/orders', $this->pathFunction(null)->getPath('orders'));

        $profiler = new Request(['panel' => 'request'], [], [], [], [], ['REQUEST_URI' => '/_profiler/abc?panel=request']);
        self::assertSame('/orders', $this->pathFunction($profiler)->getPath('orders'));
    }

    public function testPathOverridesTwigCorePathFunction(): void
    {
        $request = new Request(['shop' => 's1'], [], [], [], [], ['REQUEST_URI' => '/app']);
        $twig    = new Environment(new ArrayLoader(['t' => "{{ path('orders') }}"]));
        $twig->addExtension($this->pathFunction($request));

        self::assertSame('/orders?shop=s1', $twig->render('t'));
        self::assertSame('path', $this->pathFunction(null)->getFunctions()[0]->getName());
    }
}
