<?php

namespace PanKrok\ShoperAppstoreBundle\Model;

use PanKrok\ShoperAppstoreBundle\Controller\API\Client\BearerInterface;

class RequestModel
{
    protected BearerInterface $client;
    protected mixed $bulk = false;
    protected array $body = [];
    protected ?string $filters = null;
    protected ?array $order = null;
    protected int $limit = 20;
    protected int $page = 0;
    protected ?string $method = null;
    protected string $url = '';

    public function __construct(BearerInterface $client, mixed $bulk = false)
    {
        $this->client = $client;
        $this->bulk   = $bulk;
    }

    public function setBody(array $body): static
    {
        $this->body = $body;

        return $this;
    }

    public function getBody(): array
    {
        return $this->body;
    }

    public function getClient(): BearerInterface
    {
        return $this->client;
    }

    /**
     * @deprecated since 1.2.0, use getClient() instead.
     */
    public function getClinet(): BearerInterface
    {
        trigger_error(__METHOD__ . '() is deprecated, use getClient() instead.', E_USER_DEPRECATED);
        return $this->getClient();
    }

    protected function prepareBulk(string $method): array
    {
        return [
            'method' => $method,
            'path'   => '/webapi/rest/' . $this->url,
            'params' => array_filter([
                'page'    => $this->page,
                'limit'   => $this->limit,
                'filters' => $this->filters,
                'order'   => $this->order,
            ], fn($v) => $v !== null),
            'body'   => $this->body,
        ];
    }

    protected function prepareRequest(string $method, ?string $urlOverride = null): array
    {
        return [
            'method'  => $method,
            'url'     => $urlOverride ?? $this->url,
            'options' => [
                'query' => array_filter([
                    'page'    => $this->page,
                    'limit'   => $this->limit,
                    'filters' => $this->filters,
                    'order'   => $this->order,
                ], fn($v) => $v !== null),
                'json'  => $this->body,
            ],
        ];
    }
}
