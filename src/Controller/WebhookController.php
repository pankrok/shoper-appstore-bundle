<?php

namespace PanKrok\ShoperAppstoreBundle\Controller;

use PanKrok\ShoperAppstoreBundle\Exception\InvalidWebhookChecksumException;
use Symfony\Component\HttpFoundation\Request;

class WebhookController
{
    protected array $options;
    protected ?ApiController $api = null;

    public function __construct(
        protected ApiController $apiController,
    ) {
        $this->options = $apiController->getOptions();
    }

    /**
     * Verifies the X-WEBHOOK-SHA1 signature of an incoming Shoper webhook and
     * initialises the API client for the shop that sent it.
     *
     * Shoper signs the payload as sha1("<webhook id>:<secret>:<raw body>").
     * For Appstore apps the secret is derived per shop:
     * hmac_sha512("<shop license>:<webhook secret>", appstoreSecret).
     *
     * @throws InvalidWebhookChecksumException
     */
    public function checksum(Request $request, string $secret, bool $appstore = true): Request
    {
        $data      = $request->getContent();
        $webhookId = $request->headers->get('X-WEBHOOK-ID');
        $signature = $request->headers->get('X-WEBHOOK-SHA1');
        $license   = $request->headers->get('X-SHOP-LICENSE');

        if ('' === $data || null === $webhookId || null === $signature) {
            throw new InvalidWebhookChecksumException('Missing webhook payload or signature headers.');
        }

        if ($appstore) {
            if (null === $license) {
                throw new InvalidWebhookChecksumException('Missing X-SHOP-LICENSE header on Appstore webhook.');
            }
            $secret = hash_hmac('sha512', $license . ':' . $secret, $this->options['appstoreSecret']);
        }

        if (!hash_equals(sha1($webhookId . ':' . $secret . ':' . $data), $signature)) {
            throw new InvalidWebhookChecksumException('Webhook signature mismatch.');
        }

        if (null !== $license) {
            $this->api = $this->apiController;
            $this->api->initFromRequest(['shop' => $license], false);
        }

        return $request;
    }

    public function getApiClient(): ?ApiController
    {
        return $this->api;
    }
}
