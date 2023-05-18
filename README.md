### Yet another Telegram bot SDK on PHP

_Readme is under development._ 

Routes config is a list of arrays:
```php
[
    // Rule is a callable, which returns boolean. If it returns true, the route is matched.
    Router::ROUTE_KEY_RULE => static fn(Update $update): bool => $update->requestData === 'foo',
    
    // Static rule is a string user sends to your bot. It works faster, than a callable.
    // You should never use both 'rule' and 'rule_static' in a single route
    Router::ROUTE_KEY_RULE_STATIC => '/start',
    
    Router::ROUTE_KEY_MIDDLEWARES => [
        // List of middlewares to be applied to this route 
    ],
    
    // Class or its instance. Must implement UpdateHandlerInterface.
    Router::ROUTE_KEY_ACTION => FooAction::class, 
    
    // This key mut not be used with the previous one in the same route
    Router::ROUTE_KEY_ROUTES_LIST => [
        // List of nested routes
    ],
]
```
