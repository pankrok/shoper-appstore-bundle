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

    public function getAll(): array
    {
        return [
            'code'     => $this->code,
            'headers'  => $this->headers,
            'response' => $this->body,
        ];
    }
}
