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

    protected $limit = 10;

    public function request(string $method, string $url, array $options = []): ResponseInterface
    {
        $passthru = function (ChunkInterface $chunk, AsyncContext $context) {
            yield $chunk;
            $headers = $context->getHeaders();
            if ($headers['x-shop-api-calls'] === $headers['x-shop-api-limit']) {
                sleep(1);
            }
        };

        return new AsyncResponse($this->client, $method, $url, $options, $passthru);
    }
}
