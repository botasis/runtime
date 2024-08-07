# Using `botasis/runtime` with `yiisoft/config` and `yiisoft/di`

Using [yiisoft/config](https://github.com/yiisoft/config), Botasis registers and configures all necessary dependencies.
You have to set some of additional values, though:

## Params
- `$params['botasis/runtime']['bot token']` _(required)_ - Set you bot token, which you got from BotFather, here.
- `$params['botasis/runtime']['errors to ignore']` - List of errors returned by Telegram API which won't throw an exception.
    Each element should be an error text which returns in `description` key of response.  
    Useful when you know some requests may return errors, and it's ok. I.e. when callback response is not sent in time.
- `$params['botasis/runtime']['fallback handler']` - A handler for the situation when no route action was found. It's
    analog of 404 handler in web. Defaults to `DummyUpdateHandler`, which does nothing.
- `$params['botasis/runtime']['request tags']['success']` and `$params['botasis/runtime']['request tags']['error']` -
    [Request tags](../key-concepts/06-request-tags.md) configuration.
- `$params['botasis/runtime']['routes']` _(required)_ - [routes list](./04-defining-routes.md) for the Router.

## DI Definitions
You also have to define some PSR interface implementations:
- `Psr\Http\Client\ClientInterface` _(required)_
- `Psr\Http\Message\RequestFactoryInterface` _(required)_
- `Psr\Http\Message\RequestInterface` _(required)_
- `Psr\Http\Message\ResponseInterface` _(required)_
- `Psr\Http\Message\StreamFactoryInterface` _(required)_
- `Psr\Log\LoggerInterface`

If you don't know what packages to use, I suggest you [httpsoft/http-message](https://github.com/httpsoft/http-message)
for all `Psr\Http\Message\*` interfaces and [php-http/curl-client](https://github.com/php-http/curl-client)
for `Psr\Http\Client\ClientInterface`.  
Maybe they are not the best (who knows?), but these are ones personally I really like and use.
