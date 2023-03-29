<?php

namespace PanKrok\ShoperAppstoreBundle\Model\Resource;

use PanKrok\ShoperAppstoreBundle\Model\ResourceModel;
use PanKrok\ShoperAppstoreBundle\Model\ResponseModel;

final class Metafield extends ResourceModel
{
    protected $url = 'metafields';
    protected $object = 'system';

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

    public function get(array|int|null $body = null): ResponseModel
    {
        if (strlen($this->object) < 1) {
            throw new \Exception('invalit object: ' . $this->object);
        }

        $this->url = 'metafields/'.$this->object;
       
        $request = $this->prepareRequest('GET');
        if (is_int($body)) {
            $request['url'] .= '/'.$body;
        }
        
        return $this->client->request($request);
    }
}
