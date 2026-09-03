<?php

namespace PanKrok\ShoperAppstoreBundle\Controller;

use Symfony\Component\HttpClient\AsyncDecoratorTrait;
use Symfony\Component\HttpClient\Response\AsyncContext;
use Symfony\Component\HttpClient\Response\AsyncResponse;
use Symfony\Contracts\HttpClient\ChunkInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class HttpClient implements HttpClientInterface
{
    use AsyncDecoratorTrait;

    public function request(string $method, string $url, array $options = []): ResponseInterface
    {
        $passthru = function (ChunkInterface $chunk, AsyncContext $context) {
            yield $chunk;

            $headers = $context->getHeaders();
            $calls   = isset($headers['x-shop-api-calls'][0]) ? (int) $headers['x-shop-api-calls'][0] : null;
            $limit   = isset($headers['x-shop-api-limit'][0]) ? (int) $headers['x-shop-api-limit'][0] : null;

            if ($calls !== null && $limit !== null && $calls >= $limit) {
                sleep(1);
            }
        };

        return new AsyncResponse($this->client, $method, $url, $options, $passthru);
    }
}
