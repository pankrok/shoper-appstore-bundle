<?php

namespace PanKrok\ShoperAppstoreBundle\Model;

final class BulkModel extends RequestModel
{
    protected $url = 'bulk';
    private $bulkLimit = 25;

    public function __get($property)
    {
        $property = ucfirst($property);
        $class = "\\PanKrok\\ShoperAppstoreBundle\\Model\\Resource\\$property";
        if (class_exists($class)) {
            $this->bulk = new $class($this->client, true);
        } else {
            throw new \Exception('Resource '.$property.' does not exist!');
        }

        return $this;
    }

    public function __call($method, $var)
    {
        if ($this->bulk instanceof ResourceInterface) {
            if ('get' === $method || 'put' === $method || 'post' === $method || 'delete' === $method) {
                if (isset($var[1])) {
                    $this->body[] = $this->bulk->$method($var[0], $var[1]);
                } elseif (isset($var[0])) {
                    $this->body[] = $this->bulk->$method($var[0]);
                } else {
                    $this->body[] = $this->bulk->$method();
                }
                $this->addId();
            } else {
                if (isset($var[0])) {
                    $this->bulk->$method($var[0]);
                } else {
                    $this->bulk->$method();
                }
            }

            return $this;
        } else {
            throw new \Exception('You have to set resource first');
        }
    }

    public function addId($id = null)
    {
        $count = $this->count() - 1;
        if (null === $id) {
            $id = $count;
        }

        $this->body[$count] = array_merge(['id' => $id], $this->body[$count]);

        return $this;
    }

    public function send()
    {
        $request = $this->prepareRequest('POST');
        $this->body = null;

        return $this->client->request($request, true);
    }

    protected function count(): int
    {
        $c = count($this->body);
        if ($c > $this->bulkLimit) {
            throw new \Exception('Bulk body request limit exceeded', 10);
        }

        return $c;
    }
}
