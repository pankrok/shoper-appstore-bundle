<?php

namespace PanKrok\ShoperAppstoreBundle\Tests\Client;

use PanKrok\ShoperAppstoreBundle\Controller\API\Client;
use PanKrok\ShoperAppstoreBundle\Controller\API\Client\BasicAuth;
use PanKrok\ShoperAppstoreBundle\Controller\API\Client\Bearer;
use PanKrok\ShoperAppstoreBundle\Controller\API\Client\OAuth;
use PanKrok\ShoperAppstoreBundle\Controller\HttpClient;
use PanKrok\ShoperAppstoreBundle\Exception\ShoperApiException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

/**
 * OAuth::auth(), BasicAuth::auth(), the Client factory and the default HttpClient decorator.
 */
final class AdaptersTest extends TestCase
{
    /** @var array<int, array{method: string, url: string, options: array}> */
    private array $requests = [];

    private function http(MockResponse ...$responses): MockHttpClient
    {
        return new MockHttpClient(function (string $method, string $url, array $options) use (&$responses) {
            $this->requests[] = compact('method', 'url', 'options');
            return array_shift($responses);
        });
    }

    private function bodyOf(int $i): array
    {
        parse_str($this->requests[$i]['options']['body'], $body);

        return $body;
    }

    // --- OAuth -------------------------------------------------------------------

    public function testOAuthAuthExchangesCodeWithClientCredentials(): void
    {
        $client = new OAuth(['entrypoint' => 'https://shop', 'options' => ['appId' => 'id', 'appSecret' => 'secret']]);
        $client->setHttpClient($this->http(new MockResponse('{"access_token":"a","refresh_token":"r","expires_in":60}')));

        $client->auth('the-code');

        self::assertSame('POST', $this->requests[0]['method']);
        self::assertSame('https://shop/webapi/rest/oauth/token?grant_type=authorization_code', $this->requests[0]['url']);
        self::assertContains('Authorization: Basic ' . base64_encode('id:secret'), $this->requests[0]['options']['headers']);
        self::assertSame(['code' => 'the-code'], $this->bodyOf(0));
        self::assertSame('a', $client->getToken());
        self::assertSame('r', $client->getRefreshToken());
        self::assertEqualsWithDelta(time() + 60, $client->getExpired(), 2);
        self::assertFalse($client->isExpired());
    }

    public function testOAuthRefreshSendsRefreshToken(): void
    {
        $client = new OAuth(['entrypoint' => 'https://shop', 'options' => ['appId' => 'id', 'appSecret' => 'secret']]);
        $client->setHttpClient($this->http(new MockResponse('{"access_token":"a2","refresh_token":"r2"}')));
        $client->setRefreshToken('r1');

        $client->refresh();

        self::assertStringEndsWith('grant_type=refresh_token', $this->requests[0]['url']);
        self::assertSame(['refresh_token' => 'r1'], $this->bodyOf(0));
        self::assertSame('a2', $client->getToken());
        self::assertSame('r2', $client->getRefreshToken());
        self::assertEqualsWithDelta(time() + OAuth::DEFAULT_EXPIRES_IN, $client->getExpired(), 2, 'no expires_in → default lifetime');
    }

    public function testOAuthRefreshWithoutRefreshTokenIsALogicError(): void
    {
        $client = new OAuth(['entrypoint' => 'https://shop', 'options' => []]);

        $this->expectException(\LogicException::class);
        $client->refresh();
    }

    public function testOAuthErrorResponseBecomesShoperApiException(): void
    {
        $client = new OAuth(['entrypoint' => 'https://shop', 'options' => ['appId' => 'id', 'appSecret' => 'secret']]);
        $client->setHttpClient($this->http(new MockResponse('{"error":"invalid_grant","error_description":"bad code"}', ['http_code' => 400])));

        try {
            $client->auth('x');
            self::fail('expected exception');
        } catch (ShoperApiException $e) {
            self::assertSame(400, $e->getStatusCode());
            self::assertSame('invalid_grant', $e->getShoperError());
            self::assertSame('bad code', $e->getShoperErrorDescription());
        }
        self::assertNull($client->getToken());
    }

    public function testOAuthRejectsTokenResponseWithoutAccessToken(): void
    {
        $client = new OAuth(['entrypoint' => 'https://shop', 'options' => ['appId' => 'id', 'appSecret' => 'secret']]);
        $client->setHttpClient($this->http(new MockResponse('{"token_type":"bearer"}')));

        $this->expectException(ShoperApiException::class);
        $this->expectExceptionMessage('invalid_token_response');
        $client->auth('x');
    }

    // --- BasicAuth ---------------------------------------------------------------

    public function testBasicAuthObtainsTokenWithAdminCredentials(): void
    {
        $client = new BasicAuth(['entrypoint' => 'https://shop', 'options' => ['username' => 'admin', 'password' => 'pw']]);
        $client->setHttpClient($this->http(new MockResponse('{"access_token":"basic-token"}')));

        $client->auth();

        self::assertSame('https://shop/webapi/rest/auth', $this->requests[0]['url']);
        self::assertContains('Authorization: Basic ' . base64_encode('admin:pw'), $this->requests[0]['options']['headers']);
        self::assertSame('basic-token', $client->getToken());
    }

    public function testBasicAuthRequiresCredentials(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        (new BasicAuth(['entrypoint' => 'https://shop', 'options' => ['username' => 'admin']]))->auth();
    }

    public function testBasicAuthFailureIsShoperApiException(): void
    {
        $client = new BasicAuth(['entrypoint' => 'https://shop', 'options' => ['username' => 'a', 'password' => 'b']]);
        $client->setHttpClient($this->http(new MockResponse('{"error":"unauthorized_client"}', ['http_code' => 401])));

        $this->expectException(ShoperApiException::class);
        $client->auth();
    }

    public function testBasicAuthCannotRefresh(): void
    {
        $this->expectException(\BadMethodCallException::class);
        (new BasicAuth(['entrypoint' => 'https://shop', 'options' => []]))->refresh();
    }

    public function testBasicAuth401IsNotRetriedViaRefresh(): void
    {
        $client = new BasicAuth(['entrypoint' => 'https://shop', 'options' => []]);
        $client->setToken('t');
        $client->setHttpClient($this->http(new MockResponse('{"error":"unauthorized_client"}', ['http_code' => 401])));

        try {
            $client->request(['method' => 'GET', 'url' => 'products', 'options' => []]);
            self::fail('expected exception');
        } catch (ShoperApiException $e) {
            self::assertSame(401, $e->getStatusCode());
        }
        self::assertCount(1, $this->requests);
    }

    // --- Bearer misc -------------------------------------------------------------

    public function testBearerExpiryHelpersAndOptionsDefaults(): void
    {
        $client = new Bearer();

        self::assertInstanceOf(HttpClient::class, $client->getHttpClient());
        self::assertNull($client->getExpired());
        self::assertSame(['calls' => null, 'limit' => null, 'bandwidth' => null], $client->getRateLimitState());

        $client->setExpired(time() + 100);
        self::assertFalse($client->isExpired());
        self::assertTrue($client->isExpiredFromTimestamp(time() + 200));
        self::assertFalse($client->isExpiredFromTimestamp(time() + 50));

        $client->setExpired(time() - 1);
        self::assertTrue($client->isExpired());
    }

    public function testSetHttpClientOptionsReturnsNewDecoratedClient(): void
    {
        $client = new Bearer();
        $before = $client->getHttpClient();

        $client->setHttpClientOptions(['timeout' => 5]);

        self::assertNotSame($before, $client->getHttpClient(), 'withOptions() is immutable — the result must be kept');
    }

    // --- factory -----------------------------------------------------------------

    public function testFactoryBuildsKnownAdapters(): void
    {
        $opts = ['entrypoint' => 'https://shop', 'options' => []];

        self::assertInstanceOf(OAuth::class, Client::factory(Client::ADAPTER_OAUTH, $opts));
        self::assertInstanceOf(BasicAuth::class, Client::factory(Client::ADAPTER_BASIC_AUTH, $opts));
        self::assertInstanceOf(Bearer::class, Client::factory('Bearer', $opts));
    }

    public function testFactoryRejectsUnknownAdapter(): void
    {
        $this->expectExceptionMessage('Cannot load class');
        Client::factory('Nope', []);
    }

    public function testFactoryRejectsEmptyAdapterName(): void
    {
        $this->expectExceptionMessage('Adapter name');
        Client::factory('', []);
    }
}
