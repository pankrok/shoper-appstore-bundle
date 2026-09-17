<?php

namespace PanKrok\ShoperAppstoreBundle\Model;

class ResourceModel extends RequestModel implements ResourceInterface
{
    public const MAX_LIMIT = 50;

    public function setFilters(array|Filter $filters): static
    {
        if ($filters instanceof Filter) {
            $filters = $filters->toArray();
        }

        $this->filters = json_encode($filters);

        return $this;
    }

    public function getFilters(): array
    {
        if ($this->filters === null) {
            return [];
        }

        return json_decode($this->filters, true) ?? [];
    }

    public function setOrder(string $order): static
    {
        $expr   = (array) $order;
        $result = [];

        foreach ($expr as $e) {
            if (preg_match('/([a-z_0-9.]+) (asc|desc)$/i', $e)) {
                $result[] = $e;
            } elseif (preg_match('/([\+\-]?)([a-z_0-9.]+)/i', $e, $matches)) {
                $subResult = $matches[2];
                $subResult .= ('' === $matches[1] || '+' === $matches[1]) ? ' asc' : ' desc';
                $result[]   = $subResult;
            } else {
                throw new \InvalidArgumentException('Cannot understand ordering expression: "' . $e . '"');
            }
        }

        $this->order = $result;

        return $this;
    }

    public function getOrder(): array
    {
        return $this->order ?? [];
    }

    public function setLimit(int $limit): static
    {
        if ($limit < 1 || $limit > self::MAX_LIMIT) {
            throw new \InvalidArgumentException(
                sprintf('Limit must be between 1 and %d, got %d.', self::MAX_LIMIT, $limit)
            );
        }
        $this->limit = $limit;

        return $this;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function setPage(int $page): static
    {
        if ($page < 1) {
            throw new \InvalidArgumentException('Page parameter must be a positive integer (API pages start at 1).');
        }
        $this->page = $page;

        return $this;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * Starting record index; when set it is sent instead of "page".
     */
    public function setOffset(?int $offset): static
    {
        if (null !== $offset && $offset < 0) {
            throw new \InvalidArgumentException('Offset must be a non-negative integer.');
        }
        $this->offset = $offset;

        return $this;
    }

    public function getOffset(): ?int
    {
        return $this->offset;
    }

    /**
     * Parent object ID for sub-resources such as "collections/{parent}/products".
     */
    public function setParent(int $parentId): static
    {
        $this->parentId = $parentId;

        return $this;
    }

    public function getParent(): ?int
    {
        return $this->parentId;
    }

    /**
     * Walks every page of the collection and yields single objects.
     * Honours the configured filters, order and limit; starts at the current page.
     * Not available in bulk mode.
     *
     * @return \Generator<int, array>
     */
    public function iterate(): \Generator
    {
        if ($this->bulk) {
            throw new \LogicException('iterate() cannot be used on a bulk resource.');
        }

        $startPage = $this->page;
        $offset    = $this->offset;
        $this->offset = null;

        try {
            $page = $startPage;
            do {
                $this->page = $page;
                $response   = $this->get();

                foreach ($response->getList() as $item) {
                    yield $item;
                }

                $pages = $response->getPages();
                ++$page;
            } while (null !== $pages && $page <= $pages);
        } finally {
            $this->page   = $startPage;
            $this->offset = $offset;
        }
    }

    public function get(array|int|null $body = null): ResponseModel|array
    {
        if (!empty($body) && !is_int($body)) {
            $this->setBody($body);
        }

        $url = is_int($body) ? $this->baseUrl() . '/' . $body : $this->baseUrl();

        if ($this->bulk) {
            return $this->prepareBulk('GET', $url);
        }

        return $this->client->request($this->prepareRequest('GET', $url));
    }

    public function post(array $body = []): ResponseModel|array
    {
        if (!empty($body)) {
            $this->setBody($body);
        }

        $url = (isset($this->object) && $this->url === 'metafields')
            ? $this->url . '/' . $this->object
            : $this->baseUrl();

        if ($this->bulk) {
            return $this->prepareBulk('POST', $url);
        }

        return $this->client->request($this->prepareRequest('POST', $url));
    }

    public function put(int $id, array $body): ResponseModel|array
    {
        if (!empty($body)) {
            $this->setBody($body);
        }

        $url = $this->baseUrl() . '/' . $id;

        if ($this->bulk) {
            return $this->prepareBulk('PUT', $url);
        }

        return $this->client->request($this->prepareRequest('PUT', $url));
    }

    public function delete(array|int $body): ResponseModel|array
    {
        if (!empty($body) && !is_int($body)) {
            $this->setBody($body);
        }

        $url = is_int($body) ? $this->baseUrl() . '/' . $body : $this->baseUrl();

        if ($this->bulk) {
            return $this->prepareBulk('DELETE', $url);
        }

        return $this->client->request($this->prepareRequest('DELETE', $url));
    }
}
