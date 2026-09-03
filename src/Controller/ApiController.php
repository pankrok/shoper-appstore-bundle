<?php

namespace PanKrok\ShoperAppstoreBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use PanKrok\ShoperAppstoreBundle\Controller\API\Client;
use PanKrok\ShoperAppstoreBundle\Controller\API\Client\BearerInterface;
use PanKrok\ShoperAppstoreBundle\Entity\Shops;
use PanKrok\ShoperAppstoreBundle\Repository\ShopsRepository;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ApiController
{
    protected ?BearerInterface $client = null;
    protected array $apiOptions = [];
    protected array $requestParams = [];
    protected ?string $shopUrl = null;
    protected ?Shops $activeShop = null;
    protected ShopsRepository $shopsRepository;
    protected EntityManagerInterface $em;

    public function __construct(
        ParameterBagInterface $container,
        ShopsRepository $shopsRepository,
        EntityManagerInterface $em
    ) {
        $this->apiOptions = $container->get('appstore');
        $this->shopsRepository = $shopsRepository;
        $this->em = $em;

        if (isset($this->apiOptions['shopurl'])) {
            $this->shopUrl = $this->apiOptions['shopurl'];
        }
    }

    public function __get(string $property): mixed
    {
        $property = ucfirst($property);
        $class = $property === 'Bulk'
            ? "\\PanKrok\\ShoperAppstoreBundle\\Model\\BulkModel"
            : "\\PanKrok\\ShoperAppstoreBundle\\Model\\Resource\\$property";

        if (class_exists($class)) {
            return new $class($this->client);
        }

        return null;
    }

    // -------------------------------------------------------------------------
    // Current API
    // -------------------------------------------------------------------------

    /**
     * Initialise shop context from Shoper iframe query parameters.
     * Verifies the HMAC hash by default, then sets up the OAuth client.
     * Automatically refreshes the token if it expires within 24 hours.
     */
    public function initFromRequest(array $params, bool $checkHash = true): void
    {
        if (!isset($params['shop'])) {
            return;
        }

        $this->requestParams = $params;

        if (false === $this->verifyHash($checkHash)) {
            throw new \Exception('Invalid hash');
        }

        $this->activeShop = $this->shopsRepository->findOneBy(['shop' => $params['shop']]);
        $this->shopUrl = $this->activeShop->getShopUrl();
        $token = $this->activeShop->getAccessTokens();

        $this->client = Client::factory(Client::ADAPTER_OAUTH, [
            'options'    => $this->apiOptions,
            'entrypoint' => $this->shopUrl,
        ]);
        $this->client->setToken($token->getAccessToken());
        $this->client->setRefreshToken($token->getRefreshToken());
        $this->client->setExpired($token->getExpiresAt()->getTimestamp());

        if ($this->client->isExpiredFromTimestamp(time() + 60 * 60 * 24)) {
            $this->performTokenRefresh($token);
        }
    }

    /**
     * Refresh the OAuth token for a given shop.
     * Used primarily by the CRON command.
     */
    public function refreshToken(Shops $shop): void
    {
        $this->shopUrl = $shop->getShopUrl();
        $token = $shop->getAccessTokens();

        $this->client = Client::factory(Client::ADAPTER_OAUTH, [
            'options'    => $this->apiOptions,
            'entrypoint' => $this->shopUrl,
        ]);
        $this->client->setToken($token->getAccessToken());
        $this->client->setRefreshToken($token->getRefreshToken());
        $this->client->setExpired($token->getExpiresAt()->getTimestamp());

        if ($this->client->isExpiredFromTimestamp(time() + 60 * 60 * 24)) {
            $this->performTokenRefresh($token);
        }
    }

    /**
     * Switch to Basic Auth (admin username/password) mode.
     * Pass $url and $basicAuth to override YAML config values.
     */
    public function useBasicAuth(?string $url = null, array $basicAuth = []): BearerInterface
    {
        if (!empty($basicAuth)) {
            $this->apiOptions = $basicAuth;
        }

        if ($url !== null) {
            $this->shopUrl = $url;
        }

        $this->client = Client::factory(Client::ADAPTER_BASIC_AUTH, [
            'options'    => $this->apiOptions,
            'entrypoint' => $this->shopUrl,
        ]);

        return $this->client;
    }

    public function getRequestParams(): array
    {
        return $this->requestParams;
    }

    public function setHttpClient(BearerInterface $client): void
    {
        $this->client = $client;
    }

    public function getHttpClient(): BearerInterface
    {
        return $this->client;
    }

    public function setShopUrl(string $url): void
    {
        $this->shopUrl = $url;
    }

    public function getShopUrl(): ?string
    {
        return $this->shopUrl;
    }

    public function bindShop(Shops $shop): void
    {
        $this->shopUrl = $shop->getShopUrl();
        $this->activeShop = $shop;
    }

    public function getActiveShop(): ?Shops
    {
        return $this->activeShop;
    }

    /**
     * Returns true when the bundle is configured for OAuth (Appstore) mode.
     */
    public function isOAuthMode(): bool
    {
        return isset($this->apiOptions['appId']);
    }

    // -------------------------------------------------------------------------
    // Deprecated aliases — kept for backward compatibility
    // -------------------------------------------------------------------------

    /**
     * @deprecated since 1.2.0, use initFromRequest() instead.
     */
    public function setParams(array $params, bool $checkHash = true): void
    {
        trigger_error(__METHOD__ . '() is deprecated, use initFromRequest() instead.', E_USER_DEPRECATED);
        $this->initFromRequest($params, $checkHash);
    }

    /**
     * @deprecated since 1.2.0, use refreshToken() instead.
     */
    public function refreshShopToken(Shops $shop): void
    {
        trigger_error(__METHOD__ . '() is deprecated, use refreshToken() instead.', E_USER_DEPRECATED);
        $this->refreshToken($shop);
    }

    /**
     * @deprecated since 1.2.0, use useBasicAuth() instead.
     */
    public function setBasicAuth(?string $url = null, array $basicAuth = []): BearerInterface
    {
        trigger_error(__METHOD__ . '() is deprecated, use useBasicAuth() instead.', E_USER_DEPRECATED);
        return $this->useBasicAuth($url, $basicAuth);
    }

    /**
     * @deprecated since 1.2.0, use getRequestParams() instead.
     */
    public function getParams(): array
    {
        trigger_error(__METHOD__ . '() is deprecated, use getRequestParams() instead.', E_USER_DEPRECATED);
        return $this->getRequestParams();
    }

    /**
     * @deprecated since 1.2.0, use setHttpClient() instead.
     */
    public function setClient(BearerInterface $client): void
    {
        trigger_error(__METHOD__ . '() is deprecated, use setHttpClient() instead.', E_USER_DEPRECATED);
        $this->setHttpClient($client);
    }

    /**
     * @deprecated since 1.2.0, use getHttpClient() instead.
     */
    public function getClient(): BearerInterface
    {
        trigger_error(__METHOD__ . '() is deprecated, use getHttpClient() instead.', E_USER_DEPRECATED);
        return $this->getHttpClient();
    }

    /**
     * @deprecated since 1.2.0, use bindShop() instead.
     */
    public function setShop(Shops $shop): void
    {
        trigger_error(__METHOD__ . '() is deprecated, use bindShop() instead.', E_USER_DEPRECATED);
        $this->bindShop($shop);
    }

    /**
     * @deprecated since 1.2.0, use getActiveShop() instead.
     */
    public function getShop(): ?Shops
    {
        trigger_error(__METHOD__ . '() is deprecated, use getActiveShop() instead.', E_USER_DEPRECATED);
        return $this->getActiveShop();
    }

    /**
     * @deprecated since 1.2.0, use isOAuthMode() instead.
     */
    public function getAppId(): bool
    {
        trigger_error(__METHOD__ . '() is deprecated, use isOAuthMode() instead.', E_USER_DEPRECATED);
        return $this->isOAuthMode();
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function verifyHash(bool $checkHash): bool
    {
        if (false === $checkHash) {
            return true;
        }

        $params = [
            'place'     => $this->requestParams['place'],
            'shop'      => $this->requestParams['shop'],
            'timestamp' => $this->requestParams['timestamp'],
        ];

        $sentHash = $this->requestParams['hash'];
        ksort($params);

        $paramPairs = [];
        foreach ($params as $k => $v) {
            $paramPairs[] = $k . '=' . $v;
        }

        $hash = hash_hmac('sha512', implode('&', $paramPairs), $this->apiOptions['appstoreSecret']);

        return hash_equals($hash, $sentHash);
    }

    private function performTokenRefresh(object $token): void
    {
        $refreshed = $this->client->refresh()->toArray();

        $expiresIn = isset($refreshed['expires_in']) ? (int) $refreshed['expires_in'] : 7776000;
        $token->setExpiresAt(new \DateTimeImmutable('@' . (time() + $expiresIn)));
        $token->setCreatedAt(new \DateTimeImmutable('now'));
        $token->setAccessToken($refreshed['access_token']);
        $token->setRefreshToken($refreshed['refresh_token']);
        $this->em->flush($token);

        $this->client->setToken($token->getAccessToken());
        $this->client->setRefreshToken($token->getRefreshToken());
        $this->client->setExpired($token->getExpiresAt()->getTimestamp());
    }
}
