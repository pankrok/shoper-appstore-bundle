<?php

namespace PanKrok\ShoperAppstoreBundle\Model;

use PanKrok\ShoperAppstoreBundle\Controller\API\Client\BearerInterface;

class RequestModel
{
    protected $client;
    protected $bulk;
    protected $body = [];
    protected $filters = null;
    protected $order = null;
    protected $limit = 20;
    protected $page = 0;
    protected $method = null;

    public function __construct(BearerInterface $client, $bulk = false)
    {
        $this->client = $client;
        $this->bulk = $bulk;
    }

    public function setBody(array $body): RequestModel
    {
        $this->body = $body;

        return $this;
    }

    public function getBody(): array
    {
        return $this->body;
    }

    public function getClinet(): BearerInterface
    {
        return $this->client;
    }

    protected function prepareBulk($method): array
    {
        return [
            'method' => $method,
            'path' => '/webapi/rest/'.$this->url,
            'params' => ([
                'page' => $this->page,
                'limit' => $this->limit,
                'filters' => $this->filters,
                'order' => $this->order,
            ]),
            'body' => $this->body,
        ];
    }

    protected function prepareRequest($method): array
    {
        return [
            'method' => $method,
            'url' => $this->url,
            'options' => [
                'query' => [
                    'page' => $this->page,
                    'limit' => $this->limit,
                    'filters' => $this->filters,
                    'order' => $this->order,
                ],
                'json' => $this->body,
            ],
        ];
    }
}
