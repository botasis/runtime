# What is Botasis and what's the difference?

Botasis is oasis of bot creation. It is ready for:
- Simple bots. Just create a and a handler to handle user requests.
- Complex bots. Very-very complex bots with lots of different conditions and state management organized into a route tree.
- Long-running applications (RoadRunner, Swoole, etc.)

This library is heavily inspired by [PSR-15](https://www.php-fig.org/psr/psr-15/) 
([details](../key-concepts/01-psr-15-and-botasis.md)) and [yiisoft/router](https://github.com/yiisoft/router). It
creates an `Update` object instead of a ServerRequest, which passes through a middleware stack, and then goes to
a router, which may define an individual middleware stack for each route or group of routes.
