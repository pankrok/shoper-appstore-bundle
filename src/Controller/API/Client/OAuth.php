<?php

namespace PanKrok\ShoperAppstoreBundle\Controller\API\Client;

use PanKrok\ShoperAppstoreBundle\Exception\ShoperApiException;

class OAuth extends Bearer
{
    protected const OAUTH_URL = '/webapi/rest/oauth/token?grant_type=authorization_code';
    protected const OAUTH_REFRESH = '/webapi/rest/oauth/token?grant_type=refresh_token';

    public function auth(string $code)
    {
        $response = $this->client->request(
            'POST',
            $this->entrypoint . SELF::OAUTH_URL,
            [
                'auth_basic' => [
                    'username' => $this->options['appId'],
                    'password' => $this->options['appSecret'],
                ],
                'body' => [
                    'code' => $code,
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
    
    public function refresh()
    {
        $response = $this->client->request(
            'POST',
            $this->entrypoint . SELF::OAUTH_REFRESH,
            [
                'auth_basic' => [
                    'username' => $this->options['appId'],
                    'password' => $this->options['appSecret'],
                ],
                'body' => [
                    'refresh_token' => $this->getRefreshToken(),
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
}
