<?php

namespace PanKrok\ShoperAppstoreBundle\Controller\API\Client;

use PanKrok\ShoperAppstoreBundle\Controller\HttpClient;
use PanKrok\ShoperAppstoreBundle\Exception\ShoperApiException;
use PanKrok\ShoperAppstoreBundle\Model\ResponseModel;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class Bearer implements BearerInterface
{
    public const DEFAULT_MAX_RETRIES = 3;

    protected HttpClientInterface $client;
    protected array $options = [];
    protected string $entrypoint;
    protected ?string $token = null;
    protected ?string $refreshToken = null;
    protected ?int $expired = null;
    protected ?ResponseInterface $response = null;

    /** Number of automatic retries after a 429 "Too many requests" response. */
    protected int $maxRetries;
    /** Wait before sending when the leaky bucket reported by the API is full. */
    protected bool $throttle;

    /** @var callable(int): void */
    protected $sleeper;
    /** @var callable(array): void|null */
    protected $onTokenRefreshed = null;

    // Leaky-bucket state from the most recent response (X-SHOP-API-* headers).
    protected ?int $apiCalls = null;
    protected ?int $apiLimit = null;
    protected ?int $apiBandwidth = null;
    protected float $lastResponseAt = 0.0;

    public function __construct(array $options = [])
    {
        $this->options    = $options['options'] ?? [];
        $this->entrypoint = $options['entrypoint'] ?? '';
        $this->client     = new HttpClient();

        $rateLimit        = $this->options['rateLimit'] ?? [];
        $this->maxRetries = max(0, (int) ($rateLimit['maxRetries'] ?? self::DEFAULT_MAX_RETRIES));
        $this->throttle   = (bool) ($rateLimit['throttle'] ?? true);
        $this->sleeper    = static fn(int $seconds) => sleep($seconds);
    }

    public function setHttpClient(HttpClientInterface $client): void
    {
        $this->client = $client;
    }

    public function getHttpClient(): HttpClientInterface
    {
        return $this->client;
    }

    public function setHttpClientOptions(array $options): void
    {
        $this->client = $this->client->withOptions($options);
    }

    /**
     * Sends a request to /webapi/rest/<url>.
     *
     * - waits before sending if the previous response reported a full leaky bucket,
     * - retries after 429 honouring Retry-After (up to maxRetries),
     * - on 401 refreshes the OAuth token once (if the adapter supports it) and retries.
     *
     * @throws ShoperApiException on any non-200 response that could not be recovered
     */
    public function request($request, $bulk = false): ResponseModel
    {
        $url       = $this->entrypoint . '/webapi/rest/' . $request['url'];
        $options   = $request['options'] ?? [];
        $attempt   = 0;
        $refreshed = false;

        while (true) {
            $this->throttleIfNeeded();

            $options['auth_bearer'] = $this->getToken();
            $this->response = $this->client->request($request['method'], $url, $options);

            $status  = $this->response->getStatusCode();
            $headers = $this->response->getHeaders(false);
            $this->rememberRateLimit($headers);

            if (200 === $status) {
                return new ResponseModel($status, $headers, $this->response->getContent(false));
            }

            if (429 === $status && $attempt < $this->maxRetries) {
                ++$attempt;
                ($this->sleeper)($this->retryAfterSeconds($headers));
                continue;
            }

            if (401 === $status && !$refreshed && $this->tryRefreshToken()) {
                $refreshed = true;
                continue;
            }

            throw ShoperApiException::fromResponse($status, $this->response->getContent(false));
        }
    }

    /**
     * Called after an automatic token refresh with the raw token payload
     * (access_token, refresh_token, expires_in). Use it to persist the new token.
     */
    public function setOnTokenRefreshed(?callable $callback): void
    {
        $this->onTokenRefreshed = $callback;
    }

    /**
     * Replaces the sleep implementation (seconds). Useful in tests and async runtimes.
     */
    public function setSleeper(callable $sleeper): void
    {
        $this->sleeper = $sleeper;
    }

    public function setMaxRetries(int $maxRetries): void
    {
        $this->maxRetries = max(0, $maxRetries);
    }

    public function setThrottle(bool $throttle): void
    {
        $this->throttle = $throttle;
    }

    /**
     * Last known X-SHOP-API-CALLS / -LIMIT / -BANDWIDTH values.
     *
     * @return array{calls: ?int, limit: ?int, bandwidth: ?int}
     */
    public function getRateLimitState(): array
    {
        return ['calls' => $this->apiCalls, 'limit' => $this->apiLimit, 'bandwidth' => $this->apiBandwidth];
    }

    /**
     * Adapters able to obtain a new access token override this and return true on success.
     */
    protected function tryRefreshToken(): bool
    {
        return false;
    }

    protected function notifyTokenRefreshed(array $tokenData): void
    {
        if (null !== $this->onTokenRefreshed) {
            ($this->onTokenRefreshed)($tokenData);
        }
    }

    protected function rememberRateLimit(array $headers): void
    {
        $this->apiCalls       = isset($headers['x-shop-api-calls'][0]) ? (int) $headers['x-shop-api-calls'][0] : null;
        $this->apiLimit       = isset($headers['x-shop-api-limit'][0]) ? (int) $headers['x-shop-api-limit'][0] : null;
        $this->apiBandwidth   = isset($headers['x-shop-api-bandwidth'][0]) ? (int) $headers['x-shop-api-bandwidth'][0] : null;
        $this->lastResponseAt = microtime(true);
    }

    /**
     * Leaky bucket: "calls" drains by "bandwidth" every second. Estimate the current
     * level and wait just long enough for one more call to fit under "limit".
     */
    protected function throttleIfNeeded(): void
    {
        if (!$this->throttle || null === $this->apiCalls || null === $this->apiLimit) {
            return;
        }

        $bandwidth = max(1, $this->apiBandwidth ?? 1);
        $elapsed   = microtime(true) - $this->lastResponseAt;
        $estimate  = max(0.0, $this->apiCalls - $bandwidth * $elapsed);

        if ($estimate + 1 > $this->apiLimit) {
            $wait = (int) ceil(($estimate + 1 - $this->apiLimit) / $bandwidth);
            ($this->sleeper)(max(1, $wait));
            $this->apiCalls = max(0, $this->apiLimit - 1);
            $this->lastResponseAt = microtime(true);
        }
    }

    protected function retryAfterSeconds(array $headers): int
    {
        $value = $headers['retry-after'][0] ?? null;

        return max(1, (int) ($value ?? 1));
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    public function setToken(string $token): void
    {
        $this->token = $token;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setRefreshToken(string $refreshToken): void
    {
        $this->refreshToken = $refreshToken;
    }

    public function getRefreshToken(): ?string
    {
        return $this->refreshToken;
    }

    public function isExpired(): bool
    {
        return $this->expired < time();
    }

    public function isExpiredFromTimestamp(int $timestamp): bool
    {
        return $this->expired < $timestamp;
    }

    public function setExpired(int $time): void
    {
        $this->expired = $time;
    }

    public function getExpired(): ?int
    {
        return $this->expired;
    }
}
