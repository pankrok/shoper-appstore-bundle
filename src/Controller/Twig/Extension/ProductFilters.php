<?php

namespace PanKrok\ShoperAppstoreBundle\Controller\Twig\Extension;

use PanKrok\ShoperAppstoreBundle\Controller\ApiController;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class ProductFilters extends AbstractExtension
{
    protected $url;

    public function __construct(ApiController $api)
    {
        $this->url = $api->getShopUrl();
    }

    public function getFilters()
    {
        return [
            new TwigFilter('productImg', [$this, 'productImg']),
            new TwigFilter('productFrontUrl', [$this, 'productFrontUrl']),
            new TwigFilter('productAdminUrl', [$this, 'productAdminUrl']),
        ];
    }

    public function productImg(string $img)
    {
        $url = $this->url.'/userdata/public/gfx/'.$img.'.jpg';

        return $url;
    }

    public function productFrontUrl(int $id, string $name, $preview = true)
    {
        $name = str_replace('/', '', $name);
        $name = str_replace(' ', '-', $name);
        $name = str_replace('--', '-', $name);
        $url = $this->url.'/pl_PL/p/'.$name.'/'.$id;
        if (true === $preview) {
            $url .= '?preview=true';
        }

        return $url;
    }

    public function productAdminUrl(int $id)
    {
        $url = $this->url.'/admin/products/edit/id/'.$id;

        return $url;
    }
}
