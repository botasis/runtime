# UpdateAttribute

In your dynamic route rules and actions you my use dynamic count of parameters. But not only `Update` object and other
dependencies may be there, it's also possible to get typed attributes from an `Update` object as arguments! Let's look
at an example. Given an auth middleware, which creates/finds an internal user of your app when a request is coming:

```php

final readonly class AuthMiddleware implements \Botasis\Runtime\Middleware\MiddlewareInterface {
    public function __construct(private AuthService $authService) {}
    
    public function handle(Update $update, \Botasis\Runtime\UpdateHandlerInterface $handler): \Botasis\Runtime\Response\ResponseInterface {
        $user = $this->authService->registerOrFindFromTelegramUpdate($update);
        $update = $update->withAttribute('my-user', $user);

        return $handler->handle($update);
    }
}
```

Now your `Update` object contains a `user` attribute, which can be retrieved by `$update->getAttribute('user);`.
But the result of `getAttribute()` is not typed, it's `mixed`! Damn it!.

Fortunately, `botasis/runtime` has a PHP attribute called [`UpdateAttribute`](../../src/Router/UpdateAttribute.php).
It can be used in router dynamic rules and actions just like this:
```php
$rule = static function(#[UpdateAttribute('my-user')] User $user): bool {
    return $user->isAdmin();
}

$action = static function(Update $update, #[UpdateAttribute('my-user')] User $user): Response {
    // do your cool stuff
}
```
Note that we passed an attribute name into the attribute's constructor.

## More notes about `UpdateAttribute`:
- If you want your `UpdateAttribute` argument to be optional, make it nullable: `#[UpdateAttribute('foo')] ?Foo $foo` 
- All arguments are strictly typed, so you can't pass a string if you're waiting for integer. No type auto conversion.
