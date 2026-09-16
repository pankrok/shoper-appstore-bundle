<?php

namespace PanKrok\ShoperAppstoreBundle\Model\Resource;

use PanKrok\ShoperAppstoreBundle\Model\ResourceModel;

/**
 * Channels of a payment method. Sub-resource: call setParent($paymentId) first.
 * Available only for selected applications (contact appstore@shoper.pl).
 *
 *   $api->paymentChannel->setParent(2)->get();  // GET payments/2/channels
 */
final class PaymentChannel extends ResourceModel
{
    protected string $url = 'payments/{parent}/channels';
}
