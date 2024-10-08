# General Updates Processing

In this section, we'll explore the fundamental steps of processing updates received from Telegram using Botasis Runtime.
We'll cover how to retrieve updates, create Update objects using the UpdateFactory, and handle these updates within your application.

## Retrieving Updates

Before you can process updates from Telegram, you need a way to retrieve them. Telegram provides two primary methods
for getting updates: polling using the `getUpdates` method and setting up a webhook to receive updates asynchronously.

**Polling** involves making periodic requests to Telegram's servers to check for new updates. The `getUpdates` method
allows you to retrieve recent updates. It's very handy when you want to test your application locally.  
An example of getting updates with polling is available as a Symfony Console command [GetUpdatesCommand](../src/Console/GetUpdatesCommand.php).

**Webhooks** enable your bot to receive updates instantly when they occur. This method is more efficient for real-time applications.

To set up a webhook, you'll need a public HTTPS URL where Telegram can send updates. Configure your bot to use this URL
using the `setWebhook` method. When an event occurs on your bot's chat, Telegram will send an HTTP POST request to your
webhook URL with the update data.  
An example of setting a webhook is available as a Symfony Console command [SetTelegramWebhookCommand](../src/Console/SetTelegramWebhookCommand.php).

For detailed information on getting updates, refer to the [Telegram Bot API Documentation](https://core.telegram.org/bots/api#getting-updates).

## Creating Update Objects

Once you have retrieved updates, you need to create Update objects, so the `Application` class can work with them.
The UpdateFactory class simplifies this process by providing a convenient way to create Update instances from the raw update data.

Here's how you can create an Update object using the UpdateFactory:

```php
// Create an Update object from raw update data and handle it

/** @var Botasis\Runtime\Update\UpdateFactory $updateFactory */
$update = $updateFactory->create($rawUpdateData);

/** @var \Botasis\Runtime\Application $application */
$application->handle($update);
```

## Update Handling Inside Application

After creating Update objects, you should pass them to your application for processing. The `Application` class is responsible for
handling updates and executing the appropriate logic based on the update type and content.

Here is a graph describing what's going on after `$application->handle($update)` is called:

```mermaid
graph LR

subgraph Application
A[Application]

subgraph Middlewares
M1[Middleware 1]
M2[Middleware 2]
M3[Middleware N]
MR[Router Middleware]

subgraph Router
R[Router]
subgraph Route Middleware Stack
MM1[Route Middleware 1]
MMN[Route Middleware N]
end
H[Update Handler]
HF[Fallback Handler]
end
end

subgraph Emitter
E[Emitter]
end
end

A -->|"Application->handle()"| M1
M1 -->|Update| M2
M2 -->|Update| M3
M3 -->|Update| MR
MR --> R
R -->|Route is found| MM1
MM1 -->|Update| MMN
MMN -->|Update| H
R -->|Route not found| HF
H -->|Response| MMN
MMN -->|Response| MM1
MM1 -->|Response| MR
HF -->|Response| MR
MR -->|Response| M3
M3 -->|Response| M2
M2 -->|Response| M1
M1 -->|Response| A
A -->|"Emit Response (send Telegram requests)"| E
E --> RequestSuccessEvent
E --> RequestErrorEvent

linkStyle 0,1,2,3,4,5,6,7 stroke: green;
linkStyle 8 stroke: red;
linkStyle 9,10,11,12,13,14,15,16,17 stroke: #6d9cff
```

Let's revise this.
1. First of all, `Application` passes an incoming `Update` to a preconfigured middleware stack. This stack is defined
    by the `\Botasis\Runtime\Middleware\MiddlewareDispatcher::withMiddlewares()` method
2. The `Update` moves forward through the middleware stack (**the green line**)  
    _Note: the `RouterMiddleware` should **always** be the last in the stack as it won't let `Update` to go further._
3. The `RouterMiddleware` passes the `Update` to the `Router`
4. The `Router` tries to find an appropriate Update Handler for the given `Update`.  
    _Note: every Update Handler should implement the `UpdateHandlerInterface`._
5. If an Update Handler is found, it handles the `Update` object. Otherwise, the `Router` throws a `RouteNotFound`
    exception, and a Fallback Handler handles it. Nevertheless, it implements the `UpdateHandlerInterface` too, so
    any Handler returns a `Response object`.
    1. Any Route can have it's own middleware stack. If such a stack exists, the `Update` passes through it before
        it goes to the Update Handler.
6. The `Response` object passes the middleware stack (both of them) in the backwards direction (**the blue line**).
    Each middleware can modify this `Response` (actually not to modify, but create a new instance with some modifications).
7. Application passes the `Response` it got to the `Emitter`
8. If there are some `Request`s in the `Response`, `Emitter` sends them to the Telegram API
9. After each request an event is dispatched via PSR-14 `EventDispatcherInterface`. It is either `RequestSuccessEvent`
    or `RequestErrorEvent` depending on request result.

That's the overall Botasis Runtime processing schema. Now let's find out how to build your unique bot on top of it.


# Create an Update Handler

See also [explanation in details](./update-handlers.md).

In Botasis Runtime, an Update Handler is responsible for defining the actions to be taken when a specific type of update
is received from Telegram. To create a custom Update Handler, you have to implement the `UpdateHandlerInterface`.
This interface defines a single method, `handle(Update $update): ResponseInterface`, which receives the incoming update 
and processes it.

Feel free to define any dependencies you need as they will be resolved on handler creation.
In this example the LoggerInterface object is such a dependency.  
Be accurate about their definitions in your DI container.

```php
use Botasis\Client\Telegram\Request\Message\Message;
use Botasis\Runtime\Response\Response;
use Botasis\Runtime\Response\ResponseInterface;
use Botasis\Runtime\Update\Update;
use Botasis\Runtime\UpdateHandlerInterface;
use Psr\Log\LoggerInterface;

final class CustomUpdateHandler implements UpdateHandlerInterface
{
    public function __construct(private readonly LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function handle(Update $update): ResponseInterface
    {
        $this->logger->info('Handling update: ' . $update->id);
        
        // Your custom logic to handle the incoming update
        // In this example, we send a message to Telegram
        // to the chat this update came from
        $request = new Message('Hello Botasis', MessageFormat::TEXT, $update->chat->id);
        
        // Return the response to be sent to Telegram
        return (new Response($update))->withRequest($request);
    }
}
```

# Match a route with the Update Handler
The general routing looks like this:
```php
$routes = [
    '/start' => new Route(new RuleStatic('/start'), CustomUpdateHandler::class);
    'foo' => new Route(new RuleDynamic(static fn(Update $update) => $update->chat?->type === ChatType::GROUP && $update->requestData !== null), Foo::class);
    'obscene' => new Route(new RuleDynamic(static fn(Update $update) => str_contains($update->requestData, 'obscene')), BanHandler::class);
];
```

1. **Define Rules**:
    There are two types of routing rules:
    - **Static Rules (`RuleStatic` class)**: Matches a specific string sent to bot as either a message or a callback query.  
      It can be defined like this: `new RuleStatic('/start')`. This routing rule will be matched when a 
    - **Dynamic Rules (`RuleDynamic` class)**: Uses a callback for more complex matching logic.
      Definition example: `new RuleDynamic(static fn(Update $update) => $update->chat?->type === ChatType::PRIVATE)` 

2. **Create Routes and Groups**:
    - Use `Route` for individual routes, specifying the rule and action (handler).
    - Use `Group` to combine multiple routes under a common rule or middleware.

3. **Configure Router**:
    - Instantiate `Router` with defined routes.
    - Use the `match` method to find a suitable handler based on the incoming `Update`.

See also: [Defining routes](./defining-routes.md) detailed.
