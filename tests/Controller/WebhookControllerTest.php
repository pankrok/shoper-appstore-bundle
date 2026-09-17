<?php

namespace PanKrok\ShoperAppstoreBundle\Tests\Controller;

use PanKrok\ShoperAppstoreBundle\Controller\ApiController;
use PanKrok\ShoperAppstoreBundle\Controller\WebhookController;
use PanKrok\ShoperAppstoreBundle\Exception\InvalidWebhookChecksumException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class WebhookControllerTest extends TestCase
{
    private const APPSTORE_SECRET = 'appstore-secret';
    private const WEBHOOK_SECRET  = 'webhook-secret';
    private const LICENSE         = 'shop-license-123';
    private const WEBHOOK_ID      = '42';
    private const PAYLOAD         = '{"order_id":1}';

    private ApiController&MockObject $api;

    protected function setUp(): void
    {
        $this->api = $this->createMock(ApiController::class);
        $this->api->method('getOptions')->willReturn(['appstoreSecret' => self::APPSTORE_SECRET]);
    }

    private function request(array $headers, string $body = self::PAYLOAD): Request
    {
        $server = [];
        foreach ($headers as $name => $value) {
            $server['HTTP_' . strtoupper(str_replace('-', '_', $name))] = $value;
        }

        return new Request([], [], [], [], [], $server, $body);
    }

    private function appstoreSignature(string $body = self::PAYLOAD): string
    {
        $secret = hash_hmac('sha512', self::LICENSE . ':' . self::WEBHOOK_SECRET, self::APPSTORE_SECRET);

        return sha1(self::WEBHOOK_ID . ':' . $secret . ':' . $body);
    }

    public function testValidAppstoreWebhookInitialisesApiForShop(): void
    {
        $this->api->expects(self::once())
            ->method('initFromRequest')
            ->with(['shop' => self::LICENSE], false);

        $controller = new WebhookController($this->api);
        $controller->checksum($this->request([
            'X-WEBHOOK-ID'   => self::WEBHOOK_ID,
            'X-WEBHOOK-SHA1' => $this->appstoreSignature(),
            'X-SHOP-LICENSE' => self::LICENSE,
        ]), self::WEBHOOK_SECRET);

        self::assertSame($this->api, $controller->getApiClient());
    }

    public function testValidNonAppstoreWebhookUsesRawSecret(): void
    {
        $this->api->expects(self::never())->method('initFromRequest');

        $controller = new WebhookController($this->api);
        $controller->checksum($this->request([
            'X-WEBHOOK-ID'   => self::WEBHOOK_ID,
            'X-WEBHOOK-SHA1' => sha1(self::WEBHOOK_ID . ':' . self::WEBHOOK_SECRET . ':' . self::PAYLOAD),
        ]), self::WEBHOOK_SECRET, false);

        self::assertNull($controller->getApiClient());
    }

    public function testTamperedBodyIsRejected(): void
    {
        $this->expectException(InvalidWebhookChecksumException::class);

        (new WebhookController($this->api))->checksum($this->request([
            'X-WEBHOOK-ID'   => self::WEBHOOK_ID,
            'X-WEBHOOK-SHA1' => $this->appstoreSignature(),
            'X-SHOP-LICENSE' => self::LICENSE,
        ], '{"order_id":999}'), self::WEBHOOK_SECRET);
    }

    public function testMissingSignatureHeaderIsRejected(): void
    {
        $this->expectException(InvalidWebhookChecksumException::class);

        (new WebhookController($this->api))->checksum($this->request([
            'X-WEBHOOK-ID'   => self::WEBHOOK_ID,
            'X-SHOP-LICENSE' => self::LICENSE,
        ]), self::WEBHOOK_SECRET);
    }

    public function testAppstoreModeWithoutLicenseIsRejectedWithoutWarnings(): void
    {
        $this->expectException(InvalidWebhookChecksumException::class);

        (new WebhookController($this->api))->checksum($this->request([
            'X-WEBHOOK-ID'   => self::WEBHOOK_ID,
            'X-WEBHOOK-SHA1' => 'whatever',
        ]), self::WEBHOOK_SECRET);
    }

    public function testEmptyBodyIsRejected(): void
    {
        $this->expectException(InvalidWebhookChecksumException::class);

        (new WebhookController($this->api))->checksum($this->request([
            'X-WEBHOOK-ID'   => self::WEBHOOK_ID,
            'X-WEBHOOK-SHA1' => $this->appstoreSignature(''),
            'X-SHOP-LICENSE' => self::LICENSE,
        ], ''), self::WEBHOOK_SECRET);
    }
}
