<?php

namespace PanKrok\ShoperAppstoreBundle\Controller\API\Client;

use PanKrok\ShoperAppstoreBundle\Exception\ShoperApiException;

class BasicAuth extends Bearer
{
    public function auth(): mixed
    {
        if (!isset($this->options['username']) || !isset($this->options['password'])) {
            throw new \InvalidArgumentException('Shop API username and password must not be empty.');
        }

        $response = $this->client->request(
            'POST',
            $this->entrypoint . '/webapi/rest/auth',
            [
                'auth_basic' => [
                    'username' => $this->options['username'],
                    'password' => $this->options['password'],
                ],
            ]
        );

        if (200 !== $response->getStatusCode()) {
            throw ShoperApiException::fromResponse(
                $response->getStatusCode(),
                $response->getContent(false)
            );
        }

        $token = $response->toArray();
        $this->setToken($token['access_token']);

        return $response;
    }

    public function refresh(string $code = ''): never
    {
        throw new \BadMethodCallException('BasicAuth does not support token refresh.');
    }
}
