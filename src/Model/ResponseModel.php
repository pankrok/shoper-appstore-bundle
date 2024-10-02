<?php

namespace PanKrok\ShoperAppstoreBundle\Model;

class ResponseModel
{
    private $code;
    private $headers;
    private $body;

    public function __construct($code, $headers, $body)
    {
        $this->code = $code;
        $this->headers = $headers;
        $this->body = $body;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function toArray()
    {
        try {
            $return = json_decode($this->body, true);
        } catch (\Exception $e) {
            return $e;
        }

        return $return;
    }

    public function getBodyArray()
    {
        return $this->toArray();
    }

    public function getAll()
    {
        return [
            'code' => $this->code,
            'headers' => $this->headers,
            'response' => $this->body,
        ];
    }
}
