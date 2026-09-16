<?php

namespace PanKrok\ShoperAppstoreBundle\Model\Resource;

use PanKrok\ShoperAppstoreBundle\Model\ResourceModel;
use PanKrok\ShoperAppstoreBundle\Model\ResponseModel;

final class Metafield extends ResourceModel
{
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

        $url = $this->url . '/' . $this->object . (is_int($body) ? '/' . $body : '');

        if ($this->bulk) {
            return $this->prepareBulk('GET', $url);
        }

        return $this->client->request($this->prepareRequest('GET', $url));
    }
}
