<?php

namespace PanKrok\ShoperAppstoreBundle\Model\Resource;

use PanKrok\ShoperAppstoreBundle\Model\ResourceModel;
use PanKrok\ShoperAppstoreBundle\Model\ResponseModel;

final class Metafield extends ResourceModel
{
    /**
     * Object names accepted by the metafields resource (setObject()).
     * @see https://developers.shoper.pl/developers/api/object-names
     */
    public const OBJECT_SYSTEM = 'system';
    public const OBJECT_ABOUT_PAGE = 'AboutPage';
    public const OBJECT_ADDITIONAL_FIELD = 'AdditionalField';
    public const OBJECT_ATTRIBUTE = 'Attribute';
    public const OBJECT_ATTRIBUTE_GROUP = 'AttributeGroup';
    public const OBJECT_AUCTION = 'Auction';
    public const OBJECT_AUCTION_HOUSE = 'AuctionHouse';
    public const OBJECT_AUCTION_ORDER = 'AuctionOrder';
    public const OBJECT_AVAILABILITY = 'Availability';
    public const OBJECT_CATEGORY = 'Category';
    public const OBJECT_CURRENCY = 'Currency';
    public const OBJECT_DELIVERY = 'Delivery';
    public const OBJECT_GAUGE = 'Gauge';
    public const OBJECT_LANGUAGE = 'Language';
    public const OBJECT_NEWS = 'News';
    public const OBJECT_NEWS_CATEGORY = 'NewsCategory';
    public const OBJECT_NEWS_COMMENT = 'NewsComment';
    public const OBJECT_NEWS_FILE = 'NewsFile';
    public const OBJECT_OPTION = 'Option';
    public const OBJECT_OPTION_GROUP = 'OptionGroup';
    public const OBJECT_OPTION_VALUE = 'OptionValue';
    public const OBJECT_ORDER = 'Order';
    public const OBJECT_ORDER_PRODUCT = 'OrderProduct';
    public const OBJECT_PARCEL = 'Parcel';
    public const OBJECT_PAYMENT_METHOD = 'PaymentMethod';
    public const OBJECT_PRODUCT = 'Product';
    public const OBJECT_PRODUCT_GFX = 'ProductGfx';
    public const OBJECT_PRODUCT_STOCK = 'ProductStock';
    public const OBJECT_PRODUCER = 'Producer';
    public const OBJECT_PROGRESS = 'Progress';
    public const OBJECT_PROMO_CODE = 'PromoCode';
    public const OBJECT_SHIPPING = 'Shipping';
    public const OBJECT_STATUS = 'Status';
    public const OBJECT_SPECIAL_OFFER = 'SpecialOffer';
    public const OBJECT_SUBSCRIBER = 'Subscriber';
    public const OBJECT_SUBSCRIBER_GROUP = 'SubscriberGroup';
    public const OBJECT_TAX_VALUE = 'TaxValue';
    public const OBJECT_UNIT = 'Unit';
    public const OBJECT_USER = 'User';
    public const OBJECT_USER_ADDRESS = 'UserAddress';
    public const OBJECT_USER_GROUP = 'UserGroup';
    public const OBJECT_WEBHOOK = 'Webhook';
    public const OBJECT_ZONE = 'Zone';

    /** All known object names (system + the documented list). */
    public const OBJECTS = [self::OBJECT_SYSTEM, 'AboutPage', 'AdditionalField', 'Attribute', 'AttributeGroup', 'Auction', 'AuctionHouse', 'AuctionOrder', 'Availability', 'Category', 'Currency', 'Delivery', 'Gauge', 'Language', 'News', 'NewsCategory', 'NewsComment', 'NewsFile', 'Option', 'OptionGroup', 'OptionValue', 'Order', 'OrderProduct', 'Parcel', 'PaymentMethod', 'Product', 'ProductGfx', 'ProductStock', 'Producer', 'Progress', 'PromoCode', 'Shipping', 'Status', 'SpecialOffer', 'Subscriber', 'SubscriberGroup', 'TaxValue', 'Unit', 'User', 'UserAddress', 'UserGroup', 'Webhook', 'Zone'];

    protected string $url = 'metafields';
    protected string $object = 'system';

    public const TYPE_INT = 1;
    /**
     * type of float.
     */
    public const TYPE_FLOAT = 2;
    /**
     * type of string.
     */
    public const TYPE_STRING = 3;
    /**
     * type of binary data.
     */
    public const TYPE_BLOB = 4;

    public function setObject(string $object = 'system'): Metafield
    {
        $this->object = $object;

        return $this;
    }

    public function getObject(): string
    {
        return $this->object;
    }

    public function get(array|int|null $body = null): ResponseModel|array
    {
        if ('' === $this->object) {
            throw new \InvalidArgumentException('Metafield object name must not be empty.');
        }

        if (!empty($body) && !is_int($body)) {
            $this->setBody($body);
        }

        $url = $this->baseUrl() . '/' . $this->object . (is_int($body) ? '/' . $body : '');

        if ($this->bulk) {
            return $this->prepareBulk('GET', $url);
        }

        return $this->client->request($this->prepareRequest('GET', $url));
    }
}
