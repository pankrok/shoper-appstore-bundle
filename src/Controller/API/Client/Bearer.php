<?php

namespace PanKrok\ShoperAppstoreBundle\Controller\API\Client;

use PanKrok\ShoperAppstoreBundle\Model\ResponseModel;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\Response\TraceableResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use PanKrok\ShoperAppstoreBundle\Controller\HttpClient;

class Bearer extends AbstractController implements BearerInterface
{
    protected $client;
    protected $entrypoint;
    protected $token;
    protected $refreshToken;
    protected $expired;
    protected $response = null;

    public function __construct(array $options = [])
    {
        $this->options = $options['options'];
        $this->entrypoint = $options['entrypoint'];
        $this->client = new HttpClient();
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
        $this->client->withOptions($options);
    }

    public function request($request, $bulk = false): ResponseModel
    {
        $request['options']['auth_bearer'] = $this->getToken();
        $this->response = $this->client->request(
            $request['method'],
            $this->entrypoint.'/webapi/rest/'.$request['url'],
            $request['options']
        );

        if (200 !== $this->response->getStatusCode()) {
            $e = json_decode($this->response->getContent(false));
            throw new \Exception($e->error."\r\n".$e->error_description, $this->response->getStatusCode());
        }

        return new ResponseModel(
            $this->response->getStatusCode(),
            $this->response->getHeaders(false),
            $this->response->getContent(false)
        );
    }

    public function getResponse(): TraceableResponse
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
        if ($this->expired < time()) {
            return true;
        }
        
        return false;
    }
    
    public function isExpiredFromTimestamp(int $timestamp) {
        if ($this->expired < $timestamp) {
            return true;
        }
        
        return false;
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
