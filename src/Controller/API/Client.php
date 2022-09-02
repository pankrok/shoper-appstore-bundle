<?php

namespace PanKrok\ShoperAppstoreBundle\Controller\API;

use PanKrok\ShoperAppstoreBundle\Controller\API\Client\BearerInterface;

class Client
{
    public const ADAPTER_OAUTH = 'OAuth';
    public const ADAPTER_BASIC_AUTH = 'BasicAuth';

    protected $adapter = null;
    protected static $defaultAdapter = null;

    private function __construct()
    {
    }

    public static function factory(string $adapter, array $options = []): BearerInterface
    {
        if (!is_string($adapter) || empty($adapter)) {
            throw new \Exception('Adapter name must be specified in a string');
        }

        if (!is_array($options)) {
            throw new \Exception('Adapter parameters must be in an array');
        }

        $adapterNamespace = '\\PanKrok\\ShoperAppstoreBundle\\Controller\\API\\Client';
        if (isset($options['adapterNamespace'])) {
            if ('' != $options['adapterNamespace']) {
                $adapterNamespace = $options['adapterNamespace'];
            }
            unset($options['adapterNamespace']);
        }

        $adapterName = $adapterNamespace.'\\';
        $adapterName .= str_replace(' ', '\\', ucwords(str_replace('\\', ' ', $adapter)));
        if (!class_exists($adapterName)) {
            throw new \Exception('Cannot load class "'.$adapterName.'"');
        }

        $clientAdapter = new $adapterName($options);
        if (!($clientAdapter instanceof BearerInterface)) {
            throw new \Exception('Adapter class "'.$adapterName.'" does not implement \\PanKrok\\ShoperAppstoreBundle\\API\\Client\\BearerInterface');
        }

        if (null === self::$defaultAdapter) {
            self::$defaultAdapter = $clientAdapter;
        }

        return $clientAdapter;
    }

    public function getAdapter(): string
    {
        return $this->adapter;
    }

    public static function setDefaultAdapter(ClientInterface $adapter)
    {
        self::$defaultAdapter = $adapter;
    }
}
