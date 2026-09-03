<?php

namespace PanKrok\ShoperAppstoreBundle\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;

class ShoperApiException extends HttpException
{
    private string $shoperError;
    private string $shoperErrorDescription;
    private ?string $rawResponse;

    public function __construct(
        int $statusCode,
        string $shoperError = '',
        string $shoperErrorDescription = '',
        ?string $rawResponse = null,
        ?\Throwable $previous = null,
        array $headers = []
    ) {
        $this->shoperError = $shoperError;
        $this->shoperErrorDescription = $shoperErrorDescription;
        $this->rawResponse = $rawResponse;

        $message = $shoperError;
        if ($shoperErrorDescription) {
            $message .= ': ' . $shoperErrorDescription;
        }

        parent::__construct($statusCode, $message, $previous, $headers);
    }

    public static function fromResponse(int $statusCode, string $responseBody, ?\Throwable $previous = null): self
    {
        $data = json_decode($responseBody, true);

        $error = $data['error'] ?? 'api_error';
        $errorDescription = $data['error_description'] ?? $responseBody;

        return new self($statusCode, $error, $errorDescription, $responseBody, $previous);
    }

    public function getShoperError(): string
    {
        return $this->shoperError;
    }

    public function getShoperErrorDescription(): string
    {
        return $this->shoperErrorDescription;
    }

    public function getRawResponse(): ?string
    {
        return $this->rawResponse;
    }

    public function toArray(): array
    {
        return [
            'error' => $this->shoperError,
            'error_description' => $this->shoperErrorDescription,
            'status_code' => $this->getStatusCode(),
        ];
    }
}
