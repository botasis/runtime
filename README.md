# Botasis Runtime

Botasis Runtime is a powerful and versatile PHP library designed to streamline the development of Telegram bot applications.
It serves as a foundational framework for building Telegram bots by providing essential abstractions and features,
making it easier than ever to create interactive and intelligent chatbots.

## Key Features

- **Middleware Stack:** Botasis Runtime offers a robust middleware system, allowing you to easily define and organize
  the processing of incoming Telegram updates. With middleware, you can implement various behaviors and logic for your
  bot, such as authentication, message pre- and post-processing, and more.

- **Routing and Dispatching:** The library includes a flexible and efficient routing system, enabling you to define
  routes for specific Telegram commands or interactions. Route groups, inner middlewares, and route parameter capturing
  provide fine-grained control over how updates are handled.

- **Update Handling:** Botasis Runtime simplifies the management and manipulation of Telegram updates. It offers an
  intuitive API for accessing and modifying update data, making it effortless to interact with users and respond to
  their messages.

- **Framework Agnostic:** Botasis Runtime is designed to be framework-agnostic, which means it can be seamlessly integrated 
  into various PHP applications and frameworks. Whether you're using a specific PHP framework or developing a standalone
  bot application, Botasis Runtime adapts to your project's needs.

- **Extensibility:** Extend and customize the behavior of your Telegram bot
  by adding your own middlewares, actions, and custom logic. It has all extension points you may ever need.

- **Configuration:** Fine-tune bot settings, routing rules, and middleware
  stacks to create a bot that behaves exactly as you envision. No hard code, everything is configurable.

- **Scalability:** As your bot grows and evolves, Botasis Runtime enables you
  to make changes and enhancements easily, ensuring your bot remains adaptable
  to new features and user interactions.

- **Supports best practices:**
  - **PSR-enabled**. Not a single hard-coded dependency. You may use any PSR implementations you want.
  - **SOLID code**. You will especially like Dependency Inversion Principle implementation, as it allows you to pass
    anything anywhere and switch implementations with a snap of your fingers.
  - **Long-running enabled**. It is ready to be used with **RoadRunner**, **Swoole** and others long-running
    application engines. That means Botasis Runtime has *no any stateful services* and *never computes one thing twice*.

## Quick Start

The quickest way to start your app with Botasis is to use the [Botasis Application Template](https://github.com/botasis/bot-template).

If you don't want to use it, or you want to embed Botasis into your existing app, follow these steps:

1. Install Botasis Runtime and all requirements using [Composer](https://getcomposer.org/):
    ```bash
    composer require botasis/runtime httpsoft/http-message php-http/socket-client yiisoft/event-dispatcher yiisoft/di
    ```
    <details>
    <summary>Packages details</summary>

    - `botasis/runtime` - this package, required
    - `httpsoft/http-message` - An implementation of PSR-7 (HTTP Message) and PSR-17 (HTTP Factories).
        You can use any implementations you want, but personally I prefer this one.
    - `php-http/socket-client` - An implementation of PSR-18 (HTTP Client). You can use any implementation you want.
    - `yiisoft/event-dispatcher` - An implementation of PSR-14 (Event Dispatcher). You can use any implementation you want,
        but personally I prefer this one since it's a good and framework-agnostic implementation.
    - `yiisoft/di` - An implementation of PSR-11 (DI Container). You can use any implementation you want,
        but personally I prefer this one since it's a very efficient, convenient and framework-agnostic implementation.
    </details>
2. Create a new PHP script to initialize your bot. Normally a DI container will handle the most of this stuff.
    <details>
    <summary>PHP script listing</summary>

    ```php
    
    use Botasis\Client\Telegram\Client\ClientPsr;
    use Botasis\Runtime\Application;
    use Botasis\Runtime\CallableFactory;
    use Botasis\Runtime\Emitter;
    use Botasis\Runtime\Handler\DummyUpdateHandler;
    use Botasis\Runtime\Middleware\Implementation\RouterMiddleware;
    use Botasis\Runtime\Middleware\MiddlewareDispatcher;
    use Botasis\Runtime\Middleware\MiddlewareFactory;
    use Botasis\Runtime\Router\Route;
    use Botasis\Runtime\Router\Router;
    use Botasis\Runtime\Router\RuleStatic;
    use Botasis\Runtime\UpdateHandlerInterface;
    use Http\Client\Socket\Client;
    use HttpSoft\Message\RequestFactory;
    use HttpSoft\Message\StreamFactory;
    use Psr\Container\ContainerInterface;
    use Psr\EventDispatcher\EventDispatcherInterface;
    use Psr\Http\Client\ClientInterface;
    use Psr\Http\Message\RequestFactoryInterface;
    use Psr\Http\Message\StreamFactoryInterface;
    use Yiisoft\Di\Container;
    use Yiisoft\EventDispatcher\Dispatcher\Dispatcher;
    
    /**
    * @var string $token - a bot token you've got from the BotFather
    * @var ClientInterface $httpClient - an HTTP client. If you've installed the php-http/socket-client package,
    *                                    it's {@see Client}. Either it's a client of your choice.
    * @var RequestFactoryInterface $requestFactory - a PSR-17 HTTP request factory. If you've installed the httpsoft/http-message package,
    *                                                it's {@see RequestFactory}.
    * @var StreamFactoryInterface $streamFactory - a PSR-17 HTTP stream factory. If you've installed the httpsoft/http-message package,
    *                                              it's {@see StreamFactory}.
    * @var EventDispatcherInterface $eventDispatcher - a PSR-14 event dispatcher. If you've installed the yiisoft/event-dispatcher package,
    *                                                  it's {@see Dispatcher}.
    * @var ContainerInterface $container - a PST-11 DI container. If you've installed the yiisoft/di package,
    *                                      it's {@see Container}.
    */
    
    $client = new ClientPsr(
      $token,
      $httpClient,
      $requestFactory,
      $streamFactory,
    );
    $emitter = new Emitter($client, $eventDispatcher);
    
    $middlewareDispatcher = new MiddlewareDispatcher(
      new MiddlewareFactory($container, new CallableFactory($container)),
      $eventDispatcher,
    );
    
    /**
    * Routes definition. Here we define a route for the /start message. The HelloAction will be instantiated by a DI container.
    */
    $routes = [
        new Route(new RuleStatic('/start'), [HelloAction::class, 'handle']),
    ];
    
    /**
    * Middlewares definition. {@see RouterMiddleware} should be the last one.
    */
    $middlewares = [new RouterMiddleware(new Router($container, $middlewareDispatcher, ...$routes))];
    
    $middlewareDispatcher = $middlewareDispatcher->withMiddlewares();
    $application = new Application($emitter, new DummyUpdateHandler(), $middlewareDispatcher);
    ```
    </details>
3. Customize your bot by registering middleware, actions, and routes based on
   your bot's behavior and requirements.
4. Start receiving updates. You can use the [GetUpdatesCommand](src/Console/GetUpdatesCommand.php) to pull
  updates from Telegram API while working locally or [SetTelegramWebhookCommand](src/Console/SetTelegramWebhookCommand.php) to set 
  your bot's webhook address, so Telegram will send you updates itself.

That's it! You've now set up the foundation for your bot using Botasis Runtime.
You can continue to enhance your bot's functionality by customizing its
actions, middleware, and routes to create engaging and interactive experiences
for your users.


## Features
### 1. Routing
You can create routes and sub-routes for your Telegram bot. Every route consists of two parts;
- A `Rule`. When an `Update` comes from Telegram, `Router` checks whether it satisfy a rule of every route. Once a such route
    was found, its `Action` is executed. There are two types of rules:
  - `RuleStatic`. It maps to a message or a callback data in an Update. When a message or a callback comes from Telegram,
    it's text compared to every existing `RuleStatic`. Creation of such a rule is very simple: 
    `new RuleStatic('/command')`. And only when there is no suitable static rule, we go further to `RuleDynamic` list.
  - `RuleDynamic`. In contrast to `RuleStatic`, this rule type executes a callable on every `Update`. Creation of such a
    rule may look like this: 
    `new RuleDynamic(static fn(Update $update) => str_starts_with($update->requestData ?? '', '/start@myBot'))`.  
    Such a callable **MUST** return a boolean value. Callable definition should follow the
    [Extended Callable Definitions](./docs/extended-callable-definitions.md) format.
    
    [Defining routes in detail](./docs/defining-routes).
- An `Action`. It's a callable, which may be defined in an equal way as a `RuleDynamic` callable.
  But a route action return value **MUST** be either `null`/`void` or `ResponseInterface`. In all the other cases Router will
  throw an exception.

### 1. Attributes usage in routing

### 1. State management
When your application requires handling chat or user state, this feature is essential. Follow these four steps to use it:

1. Implement [StateRepositoryInterface](./src/State/StateRepositoryInterface.php).  
    You may use any existing implementation *(they will be implemented later)*.
2. Save user/chat state using the repository:
    ```php
    final class CharacterNameCommandAction
    {
    public function __construct(private StateRepositoryInterface $repository) {}
    
        public function handle(Update $update): ResponseInterface
        {
            $state = new StateJson($update->user->id, $update->chat->id, 'setting-name');
            $this->repository->save($state);
    
            return (new Response($update))
                ->withRequest(new Message(
                    'Enter your character name below',
                    MessageFormat::TEXT,
                    $update->chat->id,
                ));
        }
    }
    ```
3. Add [StateMiddleware](./src/State/StateMiddleware.php) before the router middleware.  
    This allows you to access the current state within your router actions.
    ```php
    $state = $update->getAttribute(\Botasis\Runtime\State\StateMiddleware::class);
    ```
    > **Caution!** This middleware searches for both user and chat ids. In case you need only user or only chat id,
       you have implement this logic on your own.
4. Use state in routing:
    ```php
    [
        new Route(new RuleStatic('/set_name'), CharacterNameCommandAction::class),
        new Route(
            new RuleDynamic(static fn(Update $update) => $update->getAttributes(StateMiddleware::class)?->getData() === json_encode('setting-name')),
            CharacterNameSetAction::class,
        ),
    ]
    ```

> If you prefer to create a strictly typed State object, you need to implement the
[StateInterface](./src/State/StateInterface.php) along with the
[StateRepositoryInterface](./src/State/StateRepositoryInterface.php).
