<?php

namespace PanKrok\ShoperAppstoreBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class ShoperAppstoreBundle extends Bundle
{
    public function __construct()
    {
    }

    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
