# Iframe

The Shoper allows applications registered in the Appstore to be displayed in the store's administration panel via an iframe. AppstoreBundle has a configured JS SDK Shoper loading code as well as an iframe initiating code and Aurora styles, more information about JS SDK can be found in [developers.shoper.pl](https://developers.shoper.pl/developers/appstore/shop-integration/js-sdk) documentation\
To use the TWIG template provided by AppstoreBundle in the main TWIG file of your project, attach:
```twig
{% include '@Appstore/js_sdk.html.twig' %}
```