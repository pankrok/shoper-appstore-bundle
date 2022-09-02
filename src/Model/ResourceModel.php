<?php

namespace PanKrok\ShoperAppstoreBundle\Model;

use PanKrok\ShoperAppstoreBundle\Model\ResponseModel;

class ResourceModel extends RequestModel implements ResourceInterface
{
    public function setFilters(array $filters): ResourceModel
    {
        $this->filters = json_encode($filters);

        return $this;
    }

    public function getFilters(): array
    {
        return json_decode($this->filters, true);
    }

    public function setOrder(string $order): ResourceModel
    {
        $matches = [];
        $expr = (array) $order;
        $result = [];

        foreach ($expr as $e) {
            // basic syntax, with asc/desc suffix
            if (preg_match('/([a-z_0-9.]+) (asc|desc)$/i', $e)) {
                $result[] = $e;
            } elseif (preg_match('/([\+\-]?)([a-z_0-9.]+)/i', $e, $matches)) {
                $subResult = $matches[2];
                if ('' == $matches[1] || '+' == $matches[1]) {
                    $subResult .= ' asc';
                } else {
                    $subResult .= ' desc';
                }
                $result[] = $subResult;
            } else {
                throw new \Exception('Cannot understand ordering expression');
            }
        }
        $this->order = $result;

        return $this;
    }

    public function getOrder(): array
    {
        return $this->order;
    }

    public function setLimit(int $limit): ResourceModel
    {
        if ($limit < 1 || $limit > 50) {
            throw new \Exception('Limit beyond 1-50 range');
        }
        $this->limit = $limit;

        return $this;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function setPage(int $page): ResourceModel
    {
        if ($page < 0) {
            throw new \Exception('Page parameter must be positive');
        }
        $this->page = $page;

        return $this;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function get(array|int|null $body = null): ResponseModel
    {
        if (!empty($body) && !is_int($body)) {
            $this->setBody($body);
        }

        if (is_int($body)) {
            $this->url .= '/'.$body;
        }

        if ($this->bulk) {
            return $this->prepareBulk('GET');
        }

        $request = $this->prepareRequest('GET');
        return $this->client->request($request);
    }

    public function post(array $body = []): ResponseModel
    {
        if (!empty($body)) {
            $this->setBody($body);
        }

        if ($this->bulk) {
            return $this->prepareBulk('POST');
        }

        $request = $this->prepareRequest('POST');

        return $this->client->request($request);
    }

    public function put(int $id, array $body): ResponseModel
    {
        if (!empty($body)) {
            $this->setBody($body);
        }
        $this->url .= '/'.$id;

        if ($this->bulk) {
            return $this->prepareBulk('PUT');
        }

        $request = $this->prepareRequest('PUT');

        return $this->client->request($request);
    }

    public function delete(array|int $body): ResponseModel
    {
        if (!empty($body) && !is_int($body)) {
            $this->setBody($body);
        }

        if (is_int($body)) {
            $this->url .= '/'.$body;
        }

        if ($this->bulk) {
            return $this->prepareBulk('DELETE');
        }

        $request = $this->prepareRequest('DELETE');

        return $this->client->request($request);
    }
}
