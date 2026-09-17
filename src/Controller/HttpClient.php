<?php

namespace PanKrok\ShoperAppstoreBundle\Controller;

use Symfony\Component\HttpClient\DecoratorTrait;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Default HTTP client used by the API adapters (a thin decorator over Symfony's).
 *
 * Rate limiting (X-SHOP-API-* leaky bucket, 429 / Retry-After) is handled in
 * Client\Bearer, so this class no longer sleeps inside the response stream.
 */
class HttpClient implements HttpClientInterface
{
    use DecoratorTrait;
}
