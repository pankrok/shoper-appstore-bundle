# Twig filters

All filters are registered automatically and available in every Twig template. They require the shop URL to be set on `ApiController` (happens automatically in OAuth iframe mode).

## `productImg`

Returns the URL to the original image file of a product.

```twig
{{ product.main_image.gfx_id | productImg }}
```

## `productFrontUrl`

Returns the URL to the product's storefront page. Requires the product ID and name. Appends `?preview=true` by default (pass `false` as second argument to disable).

```twig
{{ product.product_id | productFrontUrl(product.translations.pl_PL.name) }}

{# Without preview mode #}
{{ product.product_id | productFrontUrl(product.translations.pl_PL.name, false) }}
```

## `productAdminUrl`

Returns the URL to the product's edit page in the shop admin panel.

```twig
{{ product.product_id | productAdminUrl }}
```
