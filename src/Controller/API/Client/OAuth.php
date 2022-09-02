<?php

namespace PanKrok\ShoperAppstoreBundle\Controller\API\Client;

class OAuth extends Bearer
{
    protected const OAUTH_URL = '/webapi/rest/oauth/token?grant_type=authorization_code';

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
            throw new \Exception('Oauth exception: '.$response->getStatusCode()." \n".$response->getHeaders(false)." \n".$response->getContent(false)." \n");
        }

        $token = $response->toArray();
        $this->setToken($token['access_token']);

        return $response;
    }
}
