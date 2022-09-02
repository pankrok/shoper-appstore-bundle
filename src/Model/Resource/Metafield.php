<?php

namespace PanKrok\ShoperAppstoreBundle\Model\Resource;

use PanKrok\ShoperAppstoreBundle\Model\ResourceModel;
use PanKrok\ShoperAppstoreBundle\Model\ResponseModel;

final class Metafield extends ResourceModel
{
    protected $url = 'metafields';
    protected $type = 'system';

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

    public function setType(string $type = 'system'): Metafield
    {
        $this->type = $type;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function get(array|int|null $body = null): ResponseModel
    {
        if (null !== $body['type']) {
            $this->type = $body['type'];
        }

        $body = $body['body'];

        $this->url = 'metafields/'.$this->type;
        if (!empty($body) && !is_int($body)) {
            $this->setBody($body);
        }

        $request = $this->prepareRequest('GET');
        if (is_int($body)) {
            $request['url'] .= '/'.$body;
        }

        return $this->client->request($request);
    }
}
