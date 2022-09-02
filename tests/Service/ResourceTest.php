<?php

namespace App\Tests\Service;

use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Shoper\AppstoreBundle\Controller\ApiController;
use Shoper\AppstoreBundle\Model\ResourceInterface;
use Shoper\AppstoreBundle\Model\ResponseModel;
use Shoper\AppstoreBundle\Model\Resource as ShoperResource;
use Shoper\AppstoreBundle\Repository\ShopsRepository;

class ResourceTest extends KernelTestCase
{

    public function testGetResourceAboutPage(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        
        $shopRepository = $container->get(ShopsRepository::class);
        $shop = $shopRepository->find(1);

        $api = $container->get(ApiController::class);
        $api->setParams(['shop' => $shop->getShop()],false);
        $resource = $api->aboutPage;
        $this->assertInstanceOf(ShoperResource\AboutPage::class , $resource);
    }

    public function testGetResourceAdditionalField(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        
        $shopRepository = $container->get(ShopsRepository::class);
        $shop = $shopRepository->find(1);

        $api = $container->get(ApiController::class);
        $api->setParams(['shop' => $shop->getShop()],false);
        $resource = $api->additionalField;
        $this->assertInstanceOf(ShoperResource\AdditionalField::class , $resource);
    }

    public function testGetResourceApplicationConfig(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        
        $shopRepository = $container->get(ShopsRepository::class);
        $shop = $shopRepository->find(1);

        $api = $container->get(ApiController::class);
        $api->setParams(['shop' => $shop->getShop()],false);
        $resource = $api->applicationConfig;
        $this->assertInstanceOf(ShoperResource\ApplicationConfig::class , $resource);
    }

    public function testGetResourceApplicationLock(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        
        $shopRepository = $container->get(ShopsRepository::class);
        $shop = $shopRepository->find(1);

        $api = $container->get(ApiController::class);
        $api->setParams(['shop' => $shop->getShop()],false);
        $resource = $api->applicationLock;
        $this->assertInstanceOf(ShoperResource\ApplicationLock::class , $resource);
    }

    public function testGetResourceApplicationVersion(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        
        $shopRepository = $container->get(ShopsRepository::class);
        $shop = $shopRepository->find(1);

        $api = $container->get(ApiController::class);
        $api->setParams(['shop' => $shop->getShop()],false);
        $resource = $api->applicationVersion;
        $this->assertInstanceOf(ShoperResource\ApplicationVersion::class , $resource);
    }

    public function testGetResourceAttribute(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        
        $shopRepository = $container->get(ShopsRepository::class);
        $shop = $shopRepository->find(1);

        $api = $container->get(ApiController::class);
        $api->setParams(['shop' => $shop->getShop()],false);
        $resource = $api->attribute;
        $this->assertInstanceOf(ShoperResource\Attribute::class , $resource);
    }

    public function testGetResourceAttributeGroup(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        
        $shopRepository = $container->get(ShopsRepository::class);
        $shop = $shopRepository->find(1);

        $api = $container->get(ApiController::class);
        $api->setParams(['shop' => $shop->getShop()],false);
        $resource = $api->attributeGroup;
        $this->assertInstanceOf(ShoperResource\AttributeGroup::class , $resource);
    }

    public function testGetResourceAuction(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        
        $shopRepository = $container->get(ShopsRepository::class);
        $shop = $shopRepository->find(1);

        $api = $container->get(ApiController::class);
        $api->setParams(['shop' => $shop->getShop()],false);
        $resource = $api->auction;
        $this->assertInstanceOf(ShoperResource\Auction::class , $resource);
    }

    public function testGetResourceAuctionHouse(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        
        $shopRepository = $container->get(ShopsRepository::class);
        $shop = $shopRepository->find(1);

        $api = $container->get(ApiController::class);
        $api->setParams(['shop' => $shop->getShop()],false);
        $resource = $api->auctionHouse;
        $this->assertInstanceOf(ShoperResource\AuctionHouse::class , $resource);
    }

    public function testGetResourceAuctionOrder(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        
        $shopRepository = $container->get(ShopsRepository::class);
        $shop = $shopRepository->find(1);

        $api = $container->get(ApiController::class);
        $api->setParams(['shop' => $shop->getShop()],false);
        $resource = $api->auctionOrder;
        $this->assertInstanceOf(ShoperResource\AuctionOrder::class , $resource);
    }

    public function testGetResourceAvailability(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        
        $shopRepository = $container->get(ShopsRepository::class);
        $shop = $shopRepository->find(1);

        $api = $container->get(ApiController::class);
        $api->setParams(['shop' => $shop->getShop()],false);
        $resource = $api->availability;
        $this->assertInstanceOf(ShoperResource\Availability::class , $resource);
    }

    public function testGetResourceCategoriesTree(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        
        $shopRepository = $container->get(ShopsRepository::class);
        $shop = $shopRepository->find(1);

        $api = $container->get(ApiController::class);
        $api->setParams(['shop' => $shop->getShop()],false);
        $resource = $api->categoriesTree;
        $this->assertInstanceOf(ShoperResource\CategoriesTree::class , $resource);
    }

    public function testGetResourceCategory(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        
        $shopRepository = $container->get(ShopsRepository::class);
        $shop = $shopRepository->find(1);

        $api = $container->get(ApiController::class);
        $api->setParams(['shop' => $shop->getShop()],false);
        $resource = $api->category;
        $this->assertInstanceOf(ShoperResource\Category::class , $resource);
    }

    public function testGetResourceCollection(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        
        $shopRepository = $container->get(ShopsRepository::class);
        $shop = $shopRepository->find(1);

        $api = $container->get(ApiController::class);
        $api->setParams(['shop' => $shop->getShop()],false);
        $resource = $api->collection;
        $this->assertInstanceOf(ShoperResource\Collection::class , $resource);
    }

    public function testGetResourceCurrency(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        
        $shopRepository = $container->get(ShopsRepository::class);
        $shop = $shopRepository->find(1);

        $api = $container->get(ApiController::class);
        $api->setParams(['shop' => $shop->getShop()],false);
        $resource = $api->currency;
        $this->assertInstanceOf(ShoperResource\Currency::class , $resource);
    }

    public function testGetResourceDashboardActivity(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        
        $shopRepository = $container->get(ShopsRepository::class);
        $shop = $shopRepository->find(1);

        $api = $container->get(ApiController::class);
        $api->setParams(['shop' => $shop->getShop()],false);
        $resource = $api->dashboardActivity;
        $this->assertInstanceOf(ShoperResource\DashboardActivity::class , $resource);
    }

    public function testGetResourceDashboardStat(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        
        $shopRepository = $container->get(ShopsRepository::class);
        $shop = $shopRepository->find(1);

        $api = $container->get(ApiController::class);
        $api->setParams(['shop' => $shop->getShop()],false);
        $resource = $api->dashboardStat;
        $this->assertInstanceOf(ShoperResource\DashboardStat::class , $resource);
    }

    public function testGetResourceDelivery(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        
        $shopRepository = $container->get(ShopsRepository::class);
        $shop = $shopRepository->find(1);

        $api = $container->get(ApiController::class);
        $api->setParams(['shop' => $shop->getShop()],false);
        $resource = $api->delivery;
        $this->assertInstanceOf(ShoperResource\Delivery::class , $resource);
    }

    public function testGetResourceGauge(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        
        $shopRepository = $container->get(ShopsRepository::class);
        $shop = $shopRepository->find(1);

        $api = $container->get(ApiController::class);
        $api->setParams(['shop' => $shop->getShop()],false);
        $resource = $api->gauge;
        $this->assertInstanceOf(ShoperResource\Gauge::class , $resource);
    }

    public function testGetResourceGeolocationCountry(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        
        $shopRepository = $container->get(ShopsRepository::class);
        $shop = $shopRepository->find(1);

        $api = $container->get(ApiController::class);
        $api->setParams(['shop' => $shop->getShop()],false);
        $resource = $api->geolocationCountry;
        $this->assertInstanceOf(ShoperResource\GeolocationCountry::class , $resource);
    }

    public function testGetResourceGeolocationRegion(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        
        $shopRepository = $container->get(ShopsRepository::class);
        $shop = $shopRepository->find(1);

        $api = $container->get(ApiController::class);
        $api->setParams(['shop' => $shop->getShop()],false);
        $resource = $api->geolocationRegion;
        $this->assertInstanceOf(ShoperResource\GeolocationRegion::class , $resource);
    }

    public function testGetResourceGeolocationSubregion(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        
        $shopRepository = $container->get(ShopsRepository::class);
        $shop = $shopRepository->find(1);

        $api = $container->get(ApiController::class);
        $api->setParams(['shop' => $shop->getShop()],false);
        $resource = $api->geolocationSubregion;
        $this->assertInstanceOf(ShoperResource\GeolocationSubregion::class , $resource);
    }

    public function testGetResourceLanguage(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        
        $shopRepository = $container->get(ShopsRepository::class);
        $shop = $shopRepository->find(1);

        $api = $container->get(ApiController::class);
        $api->setParams(['shop' => $shop->getShop()],false);
        $resource = $api->language;
        $this->assertInstanceOf(ShoperResource\Language::class , $resource);
    }

    public function testGetResourceLoyaltyEvent(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        
        $shopRepository = $container->get(ShopsRepository::class);
        $shop = $shopRepository->find(1);

        $api = $container->get(ApiController::class);
        $api->setParams(['shop' => $shop->getShop()],false);
        $resource = $api->loyaltyEvent;
        $this->assertInstanceOf(ShoperResource\LoyaltyEvent::class , $resource);
    }

    public function testGetResourceMetafield(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        
        $shopRepository = $container->get(ShopsRepository::class);
        $shop = $shopRepository->find(1);

        $api = $container->get(ApiController::class);
        $api->setParams(['shop' => $shop->getShop()],false);
        $resource = $api->metafield;
        $this->assertInstanceOf(ShoperResource\Metafield::class , $resource);
    }

    public function testGetResourceMetafieldValue(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        
        $shopRepository = $container->get(ShopsRepository::class);
        $shop = $shopRepository->find(1);

        $api = $container->get(ApiController::class);
        $api->setParams(['shop' => $shop->getShop()],false);
        $resource = $api->metafieldValue;
        $this->assertInstanceOf(ShoperResource\MetafieldValue::class , $resource);
    }

    public function testGetResourceNews(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        
        $shopRepository = $container->get(ShopsRepository::class);
        $shop = $shopRepository->find(1);

        $api = $container->get(ApiController::class);
        $api->setParams(['shop' => $shop->getShop()],false);
        $resource = $api->news;
        $this->assertInstanceOf(ShoperResource\News::class , $resource);
    }

    public function testGetResourceNewsCategory(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        
        $shopRepository = $container->get(ShopsRepository::class);
        $shop = $shopRepository->find(1);

        $api = $container->get(ApiController::class);
        $api->setParams(['shop' => $shop->getShop()],false);
        $resource = $api->newsCategory;
        $this->assertInstanceOf(ShoperResource\NewsCategory::class , $resource);
    }

    public function testGetResourceNewsComment(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        
        $shopRepository = $container->get(ShopsRepository::class);
        $shop = $shopRepository->find(1);

        $api = $container->get(ApiController::class);
        $api->setParams(['shop' => $shop->getShop()],false);
        $resource = $api->newsComment;
        $this->assertInstanceOf(ShoperResource\NewsComment::class , $resource);
    }

    public function testGetResourceNewsFile(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        
        $shopRepository = $container->get(ShopsRepository::class);
        $shop = $shopRepository->find(1);

        $api = $container->get(ApiController::class);
        $api->setParams(['shop' => $shop->getShop()],false);
        $resource = $api->newsFile;
        $this->assertInstanceOf(ShoperResource\NewsFile::class , $resource);
    }

    public function testGetResourceNewsTag(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        
        $shopRepository = $container->get(ShopsRepository::class);
        $shop = $shopRepository->find(1);

        $api = $container->get(ApiController::class);
        $api->setParams(['shop' => $shop->getShop()],false);
        $resource = $api->newsTag;
        $this->assertInstanceOf(ShoperResource\NewsTag::class , $resource);
    }

    public function testGetResourceObjectMtime(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        
        $shopRepository = $container->get(ShopsRepository::class);
        $shop = $shopRepository->find(1);

        $api = $container->get(ApiController::class);
        $api->setParams(['shop' => $shop->getShop()],false);
        $resource = $api->objectMtime;
        $this->assertInstanceOf(ShoperResource\ObjectMtime::class , $resource);
    }

    public function testGetResourceOption(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        
        $shopRepository = $container->get(ShopsRepository::class);
        $shop = $shopRepository->find(1);

        $api = $container->get(ApiController::class);
        $api->setParams(['shop' => $shop->getShop()],false);
        $resource = $api->option;
        $this->assertInstanceOf(ShoperResource\Option::class , $resource);
    }

    public function testGetResourceOptionGroup(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        
        $shopRepository = $container->get(ShopsRepository::class);
        $shop = $shopRepository->find(1);

        $api = $container->get(ApiController::class);
        $api->setParams(['shop' => $shop->getShop()],false);
        $resource = $api->optionGroup;
        $this->assertInstanceOf(ShoperResource\OptionGroup::class , $resource);
    }

    public function testGetResourceOptionValue(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        
        $shopRepository = $container->get(ShopsRepository::class);
        $shop = $shopRepository->find(1);

        $api = $container->get(ApiController::class);
        $api->setParams(['shop' => $shop->getShop()],false);
        $resource = $api->optionValue;
        $this->assertInstanceOf(ShoperResource\OptionValue::class , $resource);
    }

    public function testGetResourceOrder(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        
        $shopRepository = $container->get(ShopsRepository::class);
        $shop = $shopRepository->find(1);

        $api = $container->get(ApiController::class);
        $api->setParams(['shop' => $shop->getShop()],false);
        $resource = $api->order;
        $this->assertInstanceOf(ShoperResource\Order::class , $resource);
    }

    public function testGetResourceOrderProduct(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        
        $shopRepository = $container->get(ShopsRepository::class);
        $shop = $shopRepository->find(1);

        $api = $container->get(ApiController::class);
        $api->setParams(['shop' => $shop->getShop()],false);
        $resource = $api->orderProduct;
        $this->assertInstanceOf(ShoperResource\OrderProduct::class , $resource);
    }

    public function testGetResourceOrderTag(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        
        $shopRepository = $container->get(ShopsRepository::class);
        $shop = $shopRepository->find(1);

        $api = $container->get(ApiController::class);
        $api->setParams(['shop' => $shop->getShop()],false);
        $resource = $api->orderTag;
        $this->assertInstanceOf(ShoperResource\OrderTag::class , $resource);
    }

    public function testGetResourceParcel(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        
        $shopRepository = $container->get(ShopsRepository::class);
        $shop = $shopRepository->find(1);

        $api = $container->get(ApiController::class);
        $api->setParams(['shop' => $shop->getShop()],false);
        $resource = $api->parcel;
        $this->assertInstanceOf(ShoperResource\Parcel::class , $resource);
    }

    public function testGetResourcePayment(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        
        $shopRepository = $container->get(ShopsRepository::class);
        $shop = $shopRepository->find(1);

        $api = $container->get(ApiController::class);
        $api->setParams(['shop' => $shop->getShop()],false);
        $resource = $api->payment;
        $this->assertInstanceOf(ShoperResource\Payment::class , $resource);
    }

    public function testGetResourceProducer(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        
        $shopRepository = $container->get(ShopsRepository::class);
        $shop = $shopRepository->find(1);

        $api = $container->get(ApiController::class);
        $api->setParams(['shop' => $shop->getShop()],false);
        $resource = $api->producer;
        $this->assertInstanceOf(ShoperResource\Producer::class , $resource);
    }

    public function testGetResourceProduct(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        
        $shopRepository = $container->get(ShopsRepository::class);
        $shop = $shopRepository->find(1);

        $api = $container->get(ApiController::class);
        $api->setParams(['shop' => $shop->getShop()],false);
        $resource = $api->product;
        $this->assertInstanceOf(ShoperResource\Product::class , $resource);
    }

    public function testGetResourceProductFile(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        
        $shopRepository = $container->get(ShopsRepository::class);
        $shop = $shopRepository->find(1);

        $api = $container->get(ApiController::class);
        $api->setParams(['shop' => $shop->getShop()],false);
        $resource = $api->productFile;
        $this->assertInstanceOf(ShoperResource\ProductFile::class , $resource);
    }

    public function testGetResourceProductImage(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        
        $shopRepository = $container->get(ShopsRepository::class);
        $shop = $shopRepository->find(1);

        $api = $container->get(ApiController::class);
        $api->setParams(['shop' => $shop->getShop()],false);
        $resource = $api->productImage;
        $this->assertInstanceOf(ShoperResource\ProductImage::class , $resource);
    }

    public function testGetResourceProductStock(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        
        $shopRepository = $container->get(ShopsRepository::class);
        $shop = $shopRepository->find(1);

        $api = $container->get(ApiController::class);
        $api->setParams(['shop' => $shop->getShop()],false);
        $resource = $api->productStock;
        $this->assertInstanceOf(ShoperResource\ProductStock::class , $resource);
    }

    public function testGetResourceProductTag(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        
        $shopRepository = $container->get(ShopsRepository::class);
        $shop = $shopRepository->find(1);

        $api = $container->get(ApiController::class);
        $api->setParams(['shop' => $shop->getShop()],false);
        $resource = $api->productTag;
        $this->assertInstanceOf(ShoperResource\ProductTag::class , $resource);
    }

    public function testGetResourceProgress(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        
        $shopRepository = $container->get(ShopsRepository::class);
        $shop = $shopRepository->find(1);

        $api = $container->get(ApiController::class);
        $api->setParams(['shop' => $shop->getShop()],false);
        $resource = $api->progress;
        $this->assertInstanceOf(ShoperResource\Progress::class , $resource);
    }

    public function testGetResourcePromotionCode(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        
        $shopRepository = $container->get(ShopsRepository::class);
        $shop = $shopRepository->find(1);

        $api = $container->get(ApiController::class);
        $api->setParams(['shop' => $shop->getShop()],false);
        $resource = $api->promotionCode;
        $this->assertInstanceOf(ShoperResource\PromotionCode::class , $resource);
    }

    public function testGetResourceRedirect(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        
        $shopRepository = $container->get(ShopsRepository::class);
        $shop = $shopRepository->find(1);

        $api = $container->get(ApiController::class);
        $api->setParams(['shop' => $shop->getShop()],false);
        $resource = $api->redirect;
        $this->assertInstanceOf(ShoperResource\Redirect::class , $resource);
    }

    public function testGetResourceShipping(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        
        $shopRepository = $container->get(ShopsRepository::class);
        $shop = $shopRepository->find(1);

        $api = $container->get(ApiController::class);
        $api->setParams(['shop' => $shop->getShop()],false);
        $resource = $api->shipping;
        $this->assertInstanceOf(ShoperResource\Shipping::class , $resource);
    }

    public function testGetResourceSpecialOffer(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        
        $shopRepository = $container->get(ShopsRepository::class);
        $shop = $shopRepository->find(1);

        $api = $container->get(ApiController::class);
        $api->setParams(['shop' => $shop->getShop()],false);
        $resource = $api->specialOffer;
        $this->assertInstanceOf(ShoperResource\SpecialOffer::class , $resource);
    }

    public function testGetResourceStatus(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        
        $shopRepository = $container->get(ShopsRepository::class);
        $shop = $shopRepository->find(1);

        $api = $container->get(ApiController::class);
        $api->setParams(['shop' => $shop->getShop()],false);
        $resource = $api->status;
        $this->assertInstanceOf(ShoperResource\Status::class , $resource);
    }

    public function testGetResourceSubscriber(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        
        $shopRepository = $container->get(ShopsRepository::class);
        $shop = $shopRepository->find(1);

        $api = $container->get(ApiController::class);
        $api->setParams(['shop' => $shop->getShop()],false);
        $resource = $api->subscriber;
        $this->assertInstanceOf(ShoperResource\Subscriber::class , $resource);
    }

    public function testGetResourceSubscriberGroup(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        
        $shopRepository = $container->get(ShopsRepository::class);
        $shop = $shopRepository->find(1);

        $api = $container->get(ApiController::class);
        $api->setParams(['shop' => $shop->getShop()],false);
        $resource = $api->subscriberGroup;
        $this->assertInstanceOf(ShoperResource\SubscriberGroup::class , $resource);
    }

    public function testGetResourceTax(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        
        $shopRepository = $container->get(ShopsRepository::class);
        $shop = $shopRepository->find(1);

        $api = $container->get(ApiController::class);
        $api->setParams(['shop' => $shop->getShop()],false);
        $resource = $api->tax;
        $this->assertInstanceOf(ShoperResource\Tax::class , $resource);
    }

    public function testGetResourceUnit(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        
        $shopRepository = $container->get(ShopsRepository::class);
        $shop = $shopRepository->find(1);

        $api = $container->get(ApiController::class);
        $api->setParams(['shop' => $shop->getShop()],false);
        $resource = $api->unit;
        $this->assertInstanceOf(ShoperResource\Unit::class , $resource);
    }

    public function testGetResourceUser(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        
        $shopRepository = $container->get(ShopsRepository::class);
        $shop = $shopRepository->find(1);

        $api = $container->get(ApiController::class);
        $api->setParams(['shop' => $shop->getShop()],false);
        $resource = $api->user;
        $this->assertInstanceOf(ShoperResource\User::class , $resource);
    }

    public function testGetResourceUserAddress(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        
        $shopRepository = $container->get(ShopsRepository::class);
        $shop = $shopRepository->find(1);

        $api = $container->get(ApiController::class);
        $api->setParams(['shop' => $shop->getShop()],false);
        $resource = $api->userAddress;
        $this->assertInstanceOf(ShoperResource\UserAddress::class , $resource);
    }

    public function testGetResourceUserGroup(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        
        $shopRepository = $container->get(ShopsRepository::class);
        $shop = $shopRepository->find(1);

        $api = $container->get(ApiController::class);
        $api->setParams(['shop' => $shop->getShop()],false);
        $resource = $api->userGroup;
        $this->assertInstanceOf(ShoperResource\UserGroup::class , $resource);
    }

    public function testGetResourceWarehouse(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        
        $shopRepository = $container->get(ShopsRepository::class);
        $shop = $shopRepository->find(1);

        $api = $container->get(ApiController::class);
        $api->setParams(['shop' => $shop->getShop()],false);
        $resource = $api->warehouse;
        $this->assertInstanceOf(ShoperResource\Warehouse::class , $resource);
    }

    public function testGetResourceWarehouseLog(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        
        $shopRepository = $container->get(ShopsRepository::class);
        $shop = $shopRepository->find(1);

        $api = $container->get(ApiController::class);
        $api->setParams(['shop' => $shop->getShop()],false);
        $resource = $api->warehouseLog;
        $this->assertInstanceOf(ShoperResource\WarehouseLog::class , $resource);
    }

    public function testGetResourceWarehouseRelocation(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        
        $shopRepository = $container->get(ShopsRepository::class);
        $shop = $shopRepository->find(1);

        $api = $container->get(ApiController::class);
        $api->setParams(['shop' => $shop->getShop()],false);
        $resource = $api->warehouseRelocation;
        $this->assertInstanceOf(ShoperResource\WarehouseRelocation::class , $resource);
    }

    public function testGetResourceWebhook(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        
        $shopRepository = $container->get(ShopsRepository::class);
        $shop = $shopRepository->find(1);

        $api = $container->get(ApiController::class);
        $api->setParams(['shop' => $shop->getShop()],false);
        $resource = $api->webhook;
        $this->assertInstanceOf(ShoperResource\Webhook::class , $resource);
    }

    public function testGetResourceZone(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        
        $shopRepository = $container->get(ShopsRepository::class);
        $shop = $shopRepository->find(1);

        $api = $container->get(ApiController::class);
        $api->setParams(['shop' => $shop->getShop()],false);
        $resource = $api->zone;
        $this->assertInstanceOf(ShoperResource\Zone::class , $resource);
    }
}