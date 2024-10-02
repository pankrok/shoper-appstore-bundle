
# ApiController

ApiController is main object that can talk with shoper RestAPI

## ApiController functions:

### setParams(array $params, bool $checkHash = true): void  
setting pararms of application creditionals or user creditionals, Hash check is required only for application instance in iframe

### refreshShopToken(Shops $shop): void
sending request for token refresh for choosen shop entiti

### setBasicAuth(string $url = null, array $basicAuth = []): BearerInterface  
allow to use user/password api authentication

### getParams()  
return all parameters of ApiController

### setClient(BearerInterface $client): void  
allow to insert custom HTTP client

### getClient()  
returns current HTTP client

### setShopUrl(string $url): void
allow to set current shop URL

### getShopUrl(): ?string  
return shop url stirng

### setShop(Shops $shop)  
allows you to set the entity of the store to communicate after RestAPI

### getShop(): ?Shops  
returning current shop entity

### getAppId(): bool
return true if bundle use appId

