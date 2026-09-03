<?php

namespace PanKrok\ShoperAppstoreBundle\Controller\API\Client;

use PanKrok\ShoperAppstoreBundle\Controller\HttpClient;
use PanKrok\ShoperAppstoreBundle\Exception\ShoperApiException;
use PanKrok\ShoperAppstoreBundle\Model\ResponseModel;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class Bearer implements BearerInterface
{
    protected HttpClientInterface $client;
    protected string $entrypoint;
    protected array $options = [];
    protected ?string $token = null;
    protected ?string $refreshToken = null;
    protected ?int $expired = null;
    protected ?ResponseInterface $response = null;

    public function __construct(array $options = [])
    {
        $this->options    = $options['options'];
        $this->entrypoint = $options['entrypoint'];
        $this->client     = new HttpClient(\Symfony\Component\HttpClient\HttpClient::create());
    }

    public function setHttpClient(HttpClientInterface $client): void
    {
        $this->client = $client;
    }

    public function getHttpClient(): HttpClientInterface
    {
        return $this->client;
    }

    public function setHttpClientOptions(array $options): void
    {
        $this->client = $this->client->withOptions($options);
    }

    public function request($request, $bulk = false): ResponseModel
    {
        $request['options']['auth_bearer'] = $this->getToken();
        $this->response = $this->client->request(
            $request['method'],
            $this->entrypoint . '/webapi/rest/' . $request['url'],
            $request['options']
        );

        if (200 !== $this->response->getStatusCode()) {
            throw ShoperApiException::fromResponse(
                $this->response->getStatusCode(),
                $this->response->getContent(false)
            );
        }

        return new ResponseModel(
            $this->response->getStatusCode(),
            $this->response->getHeaders(false),
            $this->response->getContent(false)
        );
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    public function setToken(string $token): void
    {
        $this->token = $token;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setRefreshToken(string $refreshToken): void
    {
        $this->refreshToken = $refreshToken;
    }

    public function getRefreshToken(): ?string
    {
        return $this->refreshToken;
    }

    public function isExpired(): bool
    {
        return $this->expired < time();
    }

    public function isExpiredFromTimestamp(int $timestamp): bool
    {
        return $this->expired < $timestamp;
    }

    public function setExpired(int $time): void
    {
        $this->expired = $time;
    }

    public function getExpired(): ?int
    {
        return $this->expired;
    }
}
