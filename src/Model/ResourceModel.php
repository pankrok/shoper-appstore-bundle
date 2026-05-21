<?php

namespace PanKrok\ShoperAppstoreBundle\Model;

class ResourceModel extends RequestModel implements ResourceInterface
{
    public const MAX_LIMIT = 50;

    public function setFilters(array $filters): static
    {
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
        if ($page < 0) {
            throw new \InvalidArgumentException('Page parameter must be a non-negative integer.');
        }
        $this->page = $page;

        return $this;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function get(array|int|null $body = null): ResponseModel|array
    {
        if (!empty($body) && !is_int($body)) {
            $this->setBody($body);
        }

        $url = is_int($body) ? $this->url . '/' . $body : $this->url;

        if ($this->bulk) {
            return $this->prepareBulk('GET');
        }

        return $this->client->request($this->prepareRequest('GET', $url));
    }

    public function post(array $body = []): ResponseModel|array
    {
        if (!empty($body)) {
            $this->setBody($body);
        }

        if ($this->bulk) {
            return $this->prepareBulk('POST');
        }

        if (isset($this->object) && $this->url === 'metafields') {
            $url = $this->url . '/' . $this->object;
            return $this->client->request($this->prepareRequest('POST', $url));
        }

        return $this->client->request($this->prepareRequest('POST'));
    }

    public function put(int $id, array $body): ResponseModel|array
    {
        if (!empty($body)) {
            $this->setBody($body);
        }

        $url = $this->url . '/' . $id;

        if ($this->bulk) {
            return $this->prepareBulk('PUT');
        }

        return $this->client->request($this->prepareRequest('PUT', $url));
    }

    public function delete(array|int $body): ResponseModel|array
    {
        if (!empty($body) && !is_int($body)) {
            $this->setBody($body);
        }

        $url = is_int($body) ? $this->url . '/' . $body : $this->url;

        if ($this->bulk) {
            return $this->prepareBulk('DELETE');
        }

        return $this->client->request($this->prepareRequest('DELETE', $url));
    }
}
