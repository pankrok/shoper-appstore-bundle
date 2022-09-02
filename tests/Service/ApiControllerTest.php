<?php

namespace App\Tests\Service;

use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Shoper\AppstoreBundle\Controller\ApiController;
use Shoper\AppstoreBundle\Model\ResourceInterface;
use Shoper\AppstoreBundle\Model\ResponseModel;
use Shoper\AppstoreBundle\Repository\ShopsRepository;

class ApiControllerTest extends KernelTestCase
{

    public function testResourcePostSuccess(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        
        $shopRepository = $container->get(ShopsRepository::class);
        $shop = $shopRepository->find(1);

        $api = $container->get(ApiController::class);
        $api->setParams(['shop' => $shop->getShop()],false);
        $product = $api->product;
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
            'code' => md5(time()),
            'unit_id' => 1
        ];
        $response = $product->post($data);      
        $this->assertInstanceOf(ResourceInterface::class , $product);
        $this->assertInstanceOf(ResponseModel::class , $response);
        $this->assertIsInt(intval($response->getBody()));
    }
    
    public function testResourceGetSuccess(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        
        $shopRepository = $container->get(ShopsRepository::class);
        $shop = $shopRepository->find(1);

        $api = $container->get(ApiController::class);
        $api->setParams(['shop' => $shop->getShop()],false);
        $product = $api->product;
        $response = $product->get();
        $this->assertInstanceOf(ResourceInterface::class , $product);
        $this->assertInstanceOf(ResponseModel::class , $response);
        $this->assertIsArray($response->getBodyArray());
    }
    
    public function testResourcePutSuccess(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        
        $shopRepository = $container->get(ShopsRepository::class);
        $shop = $shopRepository->find(1);

        $api = $container->get(ApiController::class);
        $api->setParams(['shop' => $shop->getShop()],false);
        $product = $api->product;
        $last = $product->setLimit(1)->setOrder('product_id desc')->get()->getBodyArray();
        $id = intval($last['list'][0]['product_id']);
        $response = $product->put($id, ['active' => false]);
        $this->assertInstanceOf(ResourceInterface::class , $product);
        $this->assertInstanceOf(ResponseModel::class , $response);
        $this->assertSame($response->getBody(), '1');
    }
    
    public function testResourceDeleteSuccess(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        
        $shopRepository = $container->get(ShopsRepository::class);
        $shop = $shopRepository->find(1);

        $api = $container->get(ApiController::class);
        $api->setParams(['shop' => $shop->getShop()],false);
        $product = $api->product;
        $last = $product->setLimit(1)->setOrder('product_id desc')->get()->getBodyArray();
        $id = intval($last['list'][0]['product_id']);
        $response = $product->delete($id);
        $this->assertInstanceOf(ResourceInterface::class , $product);
        $this->assertInstanceOf(ResponseModel::class , $response);
        $this->assertSame($response->getBody(), '1');
    }
    
    public function testResourcePutFailure(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        
        $shopRepository = $container->get(ShopsRepository::class);
        $shop = $shopRepository->find(1);

        $api = $container->get(ApiController::class);
        $api->setParams(['shop' => $shop->getShop()],false);
        $category = $api->category;
        $this->expectException(\Exception::class);
        $category->put(1, []); 
    }
    
    public function testResoureceBasicAuth():void
    {
         $options = [
            'username' => 'api',
            'password' => 'Shoper1234',
        ];
        $url = 'https://sklep78203.shoparena.pl';
        
        $token = $api->setBasicAuth($url, $options)->auth()->toArray();
        $response = $api->product->get();
        
        $this->assertInstanceOf(ResponseModel::class , $response);
        $this->assertArrayHasKey('access_token' , $token);
        $this->assertArrayHasKey('expires_in' , $token);
        $this->assertArrayHasKey('token_type' , $token);
    }
        
}
