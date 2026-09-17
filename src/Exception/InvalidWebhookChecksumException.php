<?php

namespace PanKrok\ShoperAppstoreBundle\Exception;

/**
 * Thrown by WebhookController::checksum() when an incoming webhook request
 * is missing its signature headers or the signature does not match.
 */
class InvalidWebhookChecksumException extends \RuntimeException
{
}
