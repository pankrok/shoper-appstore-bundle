# Twig filters

## productImg
Creating url to original img of product:
```twig
    {{ product.main_image.gfx_id | productImg }}
```
## productFrontUrl
Creating url to front page of product:
```twig
    {{ product.product_id | productFrontUrl }}
```

## productAdminUrl
Creating url to administraton page of product:
```twig
    {{ product.product_id | productAdminUrl }}
```