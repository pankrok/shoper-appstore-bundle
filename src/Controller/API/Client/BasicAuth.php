<?php

namespace PanKrok\ShoperAppstoreBundle\Controller\API\Client;

class BasicAuth extends Bearer
{
    public function auth()
    {
        if (!isset($this->options['username']) || !isset($this->options['password'])) {
            throw new \Exception('Shop api username and password must not be empty!');
        }
        
        $response = $this->client->request(
            'POST',
            $this->entrypoint.'/webapi/rest/auth',
            [
                'auth_basic' => [
                    'username' => $this->options['username'],
                    'password' => $this->options['password'],
                ],
            ]
        );

        $token = $response->toArray();
        $this->setToken($token['access_token']);

        return $response;
    }
}
