<?php

namespace PanKrok\ShoperAppstoreBundle\Controller\API\Client;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpClient\Response\TraceableResponse;
use PanKrok\ShoperAppstoreBundle\Model\ResponseModel;

interface BearerInterface
{
    public function setHttpClient(HttpClientInterface $client): void;
    public function getHttpClient(): HttpClientInterface;
    public function setHttpClientOptions(array $options): void;
    public function request($request, $bulk = false): ResponseModel;
    public function getResponse(): TraceableResponse;
    public function setToken(string $token): void;
    public function getToken(): ?string;
}
