<?php

namespace PanKrok\ShoperAppstoreBundle\Tests\Client;

use PanKrok\ShoperAppstoreBundle\Controller\API\Client\Bearer;
use PanKrok\ShoperAppstoreBundle\Controller\API\Client\OAuth;
use PanKrok\ShoperAppstoreBundle\Exception\ShoperApiException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class BearerTest extends TestCase
{
    private const ENTRYPOINT = 'https://shop.example';

    /** @var int[] seconds passed to the sleeper */
    private array $sleeps = [];
    /** @var array<int, array{method: string, url: string, options: array}> */
    private array $requests = [];

    private function http(array $responses): MockHttpClient
    {
        return new MockHttpClient(function (string $method, string $url, array $options) use (&$responses) {
            $this->requests[] = ['method' => $method, 'url' => $url, 'options' => $options];
            if ([] === $responses) {
                self::fail('Unexpected extra request to ' . $url);
            }

            return array_shift($responses);
        });
    }

    private function bearer(array $responses, array $options = [], string $class = Bearer::class): Bearer
    {
        $client = new $class(['entrypoint' => self::ENTRYPOINT, 'options' => $options]);
        $client->setHttpClient($this->http($responses));
        $client->setSleeper(function (int $s): void { $this->sleeps[] = $s; });
        $client->setToken('tok');

        return $client;
    }

    private function ok(string $body = '{"ok":true}', array $headers = []): MockResponse
    {
        return new MockResponse($body, ['http_code' => 200, 'response_headers' => $headers]);
    }

    private function authHeader(int $i): ?string
    {
        foreach ($this->requests[$i]['options']['headers'] ?? [] as $h) {
            if (str_starts_with(strtolower($h), 'authorization:')) {
                return trim(substr($h, 14));
            }
        }

        return null;
    }

    public function testRetriesAfter429HonouringRetryAfter(): void
    {
        $client = $this->bearer([
            new MockResponse('{"error":"temporarily_unavailable"}', ['http_code' => 429, 'response_headers' => ['Retry-After' => '4']]),
            $this->ok(),
        ]);

        $response = $client->request(['method' => 'GET', 'url' => 'products', 'options' => []]);

        self::assertSame(200, $response->getStatusCode());
        self::assertCount(2, $this->requests);
        self::assertSame([4], $this->sleeps);
    }

    public function testGivesUpAfterMaxRetries(): void
    {
        $tooMany = fn() => new MockResponse('{"error":"temporarily_unavailable"}', ['http_code' => 429]);
        $client  = $this->bearer([$tooMany(), $tooMany(), $tooMany()], ['rateLimit' => ['maxRetries' => 2]]);

        try {
            $client->request(['method' => 'GET', 'url' => 'products', 'options' => []]);
            self::fail('expected ShoperApiException');
        } catch (ShoperApiException $e) {
            self::assertSame(429, $e->getStatusCode());
        }

        self::assertCount(3, $this->requests, '1 initial + 2 retries');
        self::assertSame([1, 1], $this->sleeps, 'missing Retry-After falls back to 1s');
    }

    public function testMaxRetriesZeroDisablesRetry(): void
    {
        $client = $this->bearer([new MockResponse('', ['http_code' => 429])], ['rateLimit' => ['maxRetries' => 0]]);

        $this->expectException(ShoperApiException::class);
        $client->request(['method' => 'GET', 'url' => 'products', 'options' => []]);
    }

    public function testThrottlesWhenBucketReportedFull(): void
    {
        $full = ['X-SHOP-API-CALLS' => '10', 'X-SHOP-API-LIMIT' => '10', 'X-SHOP-API-BANDWIDTH' => '2'];
        $client = $this->bearer([$this->ok('{}', $full), $this->ok()]);

        $client->request(['method' => 'GET', 'url' => 'products', 'options' => []]);
        self::assertSame([], $this->sleeps, 'first request never throttles');
        self::assertSame(['calls' => 10, 'limit' => 10, 'bandwidth' => 2], $client->getRateLimitState());

        $client->request(['method' => 'GET', 'url' => 'products', 'options' => []]);
        self::assertSame([1], $this->sleeps, 'bucket full: wait one bandwidth tick before the next call');
    }

    public function testDoesNotThrottleWhenBucketHasRoom(): void
    {
        $room = ['X-SHOP-API-CALLS' => '3', 'X-SHOP-API-LIMIT' => '10', 'X-SHOP-API-BANDWIDTH' => '2'];
        $client = $this->bearer([$this->ok('{}', $room), $this->ok()]);

        $client->request(['method' => 'GET', 'url' => 'products', 'options' => []]);
        $client->request(['method' => 'GET', 'url' => 'products', 'options' => []]);

        self::assertSame([], $this->sleeps);
    }

    public function testThrottleCanBeDisabled(): void
    {
        $full = ['X-SHOP-API-CALLS' => '10', 'X-SHOP-API-LIMIT' => '10'];
        $client = $this->bearer([$this->ok('{}', $full), $this->ok()], ['rateLimit' => ['throttle' => false]]);

        $client->request(['method' => 'GET', 'url' => 'products', 'options' => []]);
        $client->request(['method' => 'GET', 'url' => 'products', 'options' => []]);

        self::assertSame([], $this->sleeps);
    }

    public function testOAuthRefreshesTokenOn401AndRetriesOnce(): void
    {
        $client = $this->bearer([
            new MockResponse('{"error":"unauthorized_client"}', ['http_code' => 401]),
            $this->ok('{"access_token":"new-tok","refresh_token":"new-ref","expires_in":3600}'),
            $this->ok('{"product_id":1}'),
        ], ['appId' => 'id', 'appSecret' => 'secret'], OAuth::class);
        $client->setRefreshToken('old-ref');

        $persisted = null;
        $client->setOnTokenRefreshed(function (array $data) use (&$persisted): void { $persisted = $data; });

        $response = $client->request(['method' => 'GET', 'url' => 'products/1', 'options' => []]);

        self::assertSame(['product_id' => 1], $response->toArray());
        self::assertCount(3, $this->requests);
        self::assertStringContainsString('grant_type=refresh_token', $this->requests[1]['url']);
        self::assertSame('Bearer tok', $this->authHeader(0));
        self::assertSame('Bearer new-tok', $this->authHeader(2), 'retry must use the refreshed token');
        self::assertSame('new-tok', $persisted['access_token']);
        self::assertSame('new-ref', $client->getRefreshToken());
        self::assertGreaterThan(time() + 3500, $client->getExpired());
    }

    public function testOAuthDoesNotLoopWhenRetryStillReturns401(): void
    {
        $client = $this->bearer([
            new MockResponse('{"error":"unauthorized_client"}', ['http_code' => 401]),
            $this->ok('{"access_token":"new-tok","refresh_token":"new-ref"}'),
            new MockResponse('{"error":"unauthorized_client"}', ['http_code' => 401]),
        ], ['appId' => 'id', 'appSecret' => 'secret'], OAuth::class);
        $client->setRefreshToken('old-ref');

        try {
            $client->request(['method' => 'GET', 'url' => 'products', 'options' => []]);
            self::fail('expected ShoperApiException');
        } catch (ShoperApiException $e) {
            self::assertSame(401, $e->getStatusCode());
        }

        self::assertCount(3, $this->requests, 'exactly one refresh attempt');
    }

    public function testPlainBearerDoesNotRefreshOn401(): void
    {
        $client = $this->bearer([new MockResponse('{"error":"unauthorized_client"}', ['http_code' => 401])]);

        $this->expectException(ShoperApiException::class);
        $client->request(['method' => 'GET', 'url' => 'products', 'options' => []]);
    }

    public function testOAuthWithoutRefreshTokenDoesNotRefresh(): void
    {
        $client = $this->bearer(
            [new MockResponse('{"error":"unauthorized_client"}', ['http_code' => 401])],
            ['appId' => 'id', 'appSecret' => 'secret'],
            OAuth::class
        );

        $this->expectException(ShoperApiException::class);
        $client->request(['method' => 'GET', 'url' => 'products', 'options' => []]);
        self::assertCount(1, $this->requests);
    }
}
