<?php

namespace PanKrok\ShoperAppstoreBundle\Model;

class ResponseModel
{
    private int $code;
    private array $headers;
    private string $body;

    public function __construct(int $code, array $headers, string $body)
    {
        $this->code    = $code;
        $this->headers = $headers;
        $this->body    = $body;
    }

    public function getStatusCode(): int
    {
        return $this->code;
    }

    /**
     * @deprecated since 1.2.0, use getStatusCode() instead.
     */
    public function getCode(): int
    {
        trigger_error(__METHOD__ . '() is deprecated, use getStatusCode() instead.', E_USER_DEPRECATED);
        return $this->getStatusCode();
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function toArray(): array
    {
        if ($this->body === '') {
            return [];
        }

        $decoded = json_decode($this->body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException(
                'Failed to decode API response body: ' . json_last_error_msg()
            );
        }

        return $decoded ?? [];
    }

    public function getBodyArray(): array
    {
        return $this->toArray();
    }

    /**
     * True when the body is a paginated collection ({count, pages, page, list}).
     */
    public function isCollection(): bool
    {
        $data = $this->toArray();

        return isset($data['list']) && is_array($data['list']) && array_key_exists('count', $data);
    }

    /**
     * Objects of a collection response; an empty array for single-object responses.
     */
    public function getList(): array
    {
        return $this->toArray()['list'] ?? [];
    }

    /**
     * Total number of objects matching the request (all pages), or null for non-collections.
     */
    public function getCount(): ?int
    {
        $data = $this->toArray();

        return isset($data['count']) ? (int) $data['count'] : null;
    }

    /**
     * Total number of pages, or null for non-collections.
     */
    public function getPages(): ?int
    {
        $data = $this->toArray();

        return isset($data['pages']) ? (int) $data['pages'] : null;
    }

    /**
     * Index of the returned page (1-based), or null for non-collections.
     */
    public function getPage(): ?int
    {
        $data = $this->toArray();

        return isset($data['page']) ? (int) $data['page'] : null;
    }

    public function getAll(): array
    {
        return [
            'code'     => $this->code,
            'headers'  => $this->headers,
            'response' => $this->body,
        ];
    }
}
