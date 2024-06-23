Routes are defined as a list in the `Router` class constructor (the last parameter):
`new Router($container, $middlewareDispatcher, $callableResolver, ...$routes)`. Every route consists of two parts:
a rule and an update handler. Rules are made to check if current Telegram Update should be handled with a concrete route.
Handler is subject to be called once a rule suites the current Telegram Update.

# Rules
Rules may be of two types: static and dynamic. Let's describe them separately.

## RuleStatic
Static rules are made to create blazing fast update handler mapping. While dynamic rules always call a function
on every Telegram Update, static rules are converted into a map of the form `['text' => $handler]`. That's why
you should use static rules when you want to always do the same things for, i.e., a command like `/start`:
```php
new Route(
    new RuleStatic('/start'), 
    [GreetingHandler::class, 'handle']
)
```

## RuleDynamic

The `RuleDynamic` class is used to define dynamic routing rules that execute a callable on every Telegram Update.
This would be very useful in most cases: you may check [update type/field](https://core.telegram.org/bots/api#update),
chat type (public/private), message substring or regex, current user chat state, etc.

The only restriction for such a check: your function **MUST** return a boolean value.

Example: 
```php
new Route(
    new RuleDynamic(static fn(Update $update) => $update->chat->type !== ChatType::PRIVATE
        && ($update->requestData ?? '') === '/start@myBot'),
    [PublicGreetingHandler::class, 'handle'],
)
```

Dynamic rule callables should follow the [Callable Definitions Extended](./extended-callable-definitions.md) format. 

# Route Groups

Routes may be grouped with a `Group` object. This is useful in some cases:
- You have similar route rules, which should be divided by an additional condition. I.e. the same messages will be
    handled differently in public and in private chats. Example:
- You want to add a [middleware](./middlewares.md) (or a middleware list) to a group of routes

Example:
```php
[
    (new Group(
        new RuleDynamic(fn(Update $update) => $update->chat->type === ChatType::PRIVATE),
        new Route(new RuleStatic('/start'), [GreetingHandler::class, 'handlePublic']),
        new Route(new RuleStatic('/foo'), [FooHandler::class, 'handlePublic']),
    ))->withMiddlewares(PrivateChatMiddleware::class),
    (new Group(
        new RuleDynamic(fn(Update $update) => true),
        new Route(new RuleStatic('/start'), [GreetingHandler::class, 'handlePrivate']),
        new Route(new RuleStatic('/foo'), [FooHandler::class, 'handlePrivate']),
    ))->withMiddlewares(PublicChatMiddleware::class),
];
```

# Telegram Update Handlers

Handlers are callables which are called when a route is matched against a Telegram Update. It carries out the payload.

Update Handlers should follow the [Callable Definitions Extended](./extended-callable-definitions.md) format and
**MUST** return either `ResponseInterface` or `null`/`void`. Any other return value will cause an exception.

------

Next: [Telegram Update Handler in details](./update-handlers.md).
