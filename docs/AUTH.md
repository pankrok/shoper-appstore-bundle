AppstoreBundle allows you to log in using the data generated when creating the application
```yaml
# config/packages/appstore.yaml
appstore:
    appId: appId    
    appSecret: appSecret
    appstoreSecret: appstoreSecret
```
or by creating an administrator in the store using his login and password (webapi access required)
```yaml
# config/packages/appstore.yaml
appstore:
    username: adminUsername
    password: adminPassword
    shopurl: https://shopurl.com
```

in setBasicAuth method, define the token with the method setToken(string $ token) or log in using the auth() method which will return the token.
```php
<?php

    #[Route('/index', name: 'index')]
    public function index(ApiController $api): Response
    {
        $token = $api->setBasicAuth()->auth();
        /* 
        * you can use toArray() method to recive array body of shop response:
        * array(3) { ["access_token"]=> string(40) "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx" ["expires_in"]=> int(2592000) ["token_type"]=> string(6) "bearer" } 
        */
        $data = $api->product->get()->getBodyArray();
        $data[] = $api->getClient()->getToken();

        return $this->renderForm('index/index.html.twig', [
            'controller_name' => 'IndexController',
            'r' => $data
        ]);
    }
```

setBasicAuth() allows you to use the SDK without putting any data into the YAML file.
```php
<?php

   #[Route('/index', name: 'index')]
    public function index(ApiController $api): Response
    {
        $options = [
            'username' => 'foo',
            'password' => 'bar',
        ];
        $url = 'https://foo.bar';
        $token = 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx';
        
        $api->setBasicAuth($url, $options)->setToken($token);
        $data = $api->product->get()->getBodyArray();
        $data[] = $api->getClient()->getToken();

        return $this->renderForm('index/index.html.twig', [
            'controller_name' => 'IndexController',
            'r' => $data
        ]);
    }
```

