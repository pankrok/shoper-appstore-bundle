<?php

namespace PanKrok\ShoperAppstoreBundle\Controller\API\Client;

use PanKrok\ShoperAppstoreBundle\Exception\ShoperApiException;
use Symfony\Contracts\HttpClient\ResponseInterface;

class OAuth extends Bearer
{
    protected const OAUTH_URL = '/webapi/rest/oauth/token?grant_type=authorization_code';
    protected const OAUTH_REFRESH = '/webapi/rest/oauth/token?grant_type=refresh_token';

    /** Shoper default token lifetime when expires_in is missing (90 days). */
    public const DEFAULT_EXPIRES_IN = 7776000;

    public function auth(string $code): ResponseInterface
    {
        return $this->tokenRequest(self::OAUTH_URL, ['code' => $code]);
    }

    public function refresh(): ResponseInterface
    {
        if (null === $this->getRefreshToken()) {
            throw new \LogicException('Cannot refresh: no refresh token set on the OAuth client.');
        }

        return $this->tokenRequest(self::OAUTH_REFRESH, ['refresh_token' => $this->getRefreshToken()]);
    }

    /**
     * Automatic recovery from 401: refresh the token and notify the owner so it can be persisted.
     */
    protected function tryRefreshToken(): bool
    {
        if (null === $this->getRefreshToken()) {
            return false;
        }

        $this->notifyTokenRefreshed($this->refresh()->toArray());

        return true;
    }

    private function tokenRequest(string $path, array $body): ResponseInterface
    {
        $response = $this->client->request('POST', $this->entrypoint . $path, [
            'auth_basic' => [
                'username' => $this->options['appId'],
                'password' => $this->options['appSecret'],
            ],
            'body' => $body,
        ]);

        if (200 !== $response->getStatusCode()) {
            throw ShoperApiException::fromResponse($response->getStatusCode(), $response->getContent(false));
        }

        $token = $response->toArray();
        if (empty($token['access_token'])) {
            throw new ShoperApiException(502, 'invalid_token_response', 'OAuth token endpoint returned no access_token', $response->getContent(false));
        }

        $this->setToken($token['access_token']);
        if (isset($token['refresh_token'])) {
            $this->setRefreshToken($token['refresh_token']);
        }
        $this->setExpired(time() + (int) ($token['expires_in'] ?? self::DEFAULT_EXPIRES_IN));

        return $response;
    }
}
