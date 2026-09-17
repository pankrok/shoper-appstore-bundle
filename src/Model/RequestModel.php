<?php

namespace PanKrok\ShoperAppstoreBundle\Model;

use PanKrok\ShoperAppstoreBundle\Controller\API\Client\BearerInterface;

class RequestModel
{
    /**
     * Placeholder in $url for sub-resources, e.g. "collections/{parent}/products".
     */
    public const PARENT_PLACEHOLDER = '{parent}';

    protected BearerInterface $client;
    protected mixed $bulk = false;
    protected array $body = [];
    protected ?string $filters = null;
    protected ?array $order = null;
    protected int $limit = 20;
    protected int $page = 1;
    protected ?int $offset = null;
    protected ?int $parentId = null;
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

    /**
     * Resource path with the parent ID substituted for sub-resources.
     *
     * @throws \LogicException when the resource needs a parent ID that was not set
     */
    protected function baseUrl(): string
    {
        if (!str_contains($this->url, self::PARENT_PLACEHOLDER)) {
            return $this->url;
        }

        if (null === $this->parentId) {
            throw new \LogicException(sprintf(
                'Resource "%s" is a sub-resource; call setParent($id) before making a request.',
                static::class
            ));
        }

        return str_replace(self::PARENT_PLACEHOLDER, (string) $this->parentId, $this->url);
    }

    /**
     * Query-string parameters for collection requests.
     * "offset" replaces "page" when set (API semantics).
     */
    protected function listParams(): array
    {
        return array_filter([
            'page'    => null === $this->offset ? $this->page : null,
            'offset'  => $this->offset,
            'limit'   => $this->limit,
            'filters' => $this->filters,
            'order'   => $this->order,
        ], fn($v) => $v !== null);
    }

    protected function prepareBulk(string $method, ?string $urlOverride = null): array
    {
        $call = [
            'method' => $method,
            'path'   => '/webapi/rest/' . ($urlOverride ?? $this->baseUrl()),
        ];

        if ('GET' === $method) {
            $call['params'] = $this->listParams();
        }

        if ([] !== $this->body) {
            $call['body'] = $this->body;
        }

        return $call;
    }

    protected function prepareRequest(string $method, ?string $urlOverride = null): array
    {
        return [
            'method'  => $method,
            'url'     => $urlOverride ?? $this->baseUrl(),
            'options' => [
                'query' => $this->listParams(),
                'json'  => $this->body,
            ],
        ];
    }
}
