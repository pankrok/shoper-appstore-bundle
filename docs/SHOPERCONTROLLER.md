# Creating Shoper contoller

## Index
* [Creating controller](#creating-controller)
* [Get request](#single-request)
* [Insert object](#insert-object)
* [Updating object](#updating-object)
* [Deleting object](#deleting-object)
* [Bulk Request](#bulk-Request)

## Creating controller
```bash
 php bin/console make:shoper-controller
```
maker will create a controller that allows you to manage requests sent to the store as well as receive data, below is an example of the use of a controler. 

## Get request
```php
<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use PanKrok\ShoperAppstoreBundle\Controller\ApiController;


class IndexController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(ApiController $api): Response
    {
        $data = $api->product->get();
        
        return $this->renderForm('index/index.html.twig', [
            'controller_name' => 'IndexController',
            'data' => $data,
        ]);
    }
}

```

## Insert object
> Upon successful request, this method returns an identifier of created object
```php
<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use PanKrok\ShoperAppstoreBundle\Controller\AppController;


class IndexController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(ApiController $api): Response
    {
        $data = [
            'category_id' => 1,
            'producer_id' => 1,
            'translations' => [
                'pl_PL' => [
                    'name' => 'product name',
                    'description' => 'product description',
                    'active' => true
                ]
            ],
            'stock' => [
                'price' => 10,
                'active' => 1,
                'stock' => 10
            ],
            'tax_id' => 1,
            'code' => '1234567',
            'unit_id' => 1
        ];
        $result = $api->product->post($data);
        printf("An object has been added #%d", $result);
        
        return new Response();
    }
}

```

## Updating object
>Upon successful request, this method returns true.
```php
<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use PanKrok\ShoperAppstoreBundle\Controller\ApiController;


class IndexController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(ApiController $api): Response
    {
        $productId = 1;
        $data = [
            'stock' => [
                'price' => 10,
                'active' => 1,
                'stock' => 10
            ],
        ];
        $result = $api->product->put($productId, $data);
        if($result){
            echo 'A product has been successfully updated';
        }
        
        return new Response();
    }
}

```

## Delete object
>Upon successful request, no response is returned
```php
<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use PanKrok\ShoperAppstoreBundle\Controller\ApiController;


class IndexController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(ApiController $api): Response
    {
        $id = 1;
        $result = $api->product->delete($id);
        if($result){
            echo 'Product deleted';
        }
        
        return new Response();
    }
}

```

## Bulk Request
> Bulk can use all shoper RestApi methods: _get(?id) post(?$data) put($id, $data) depete($id)_
```php
<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use PanKrok\ShoperAppstoreBundle\Controller\ApiController;


class IndexController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(ApiController $api): Response
    {
        $data = $api
                    ->bulk
                        ->product
                            ->setLimit(1)
                            ->setPage(1)
                            ->get()
                        ->product
                            ->setLimit(1)
                            ->setPage(2)
                            ->get()
                        ->send();
        
        return $this->renderForm('index/index.html.twig', [
            'controller_name' => 'IndexController',
            'data' => $data,
        ]);
    }
}
```