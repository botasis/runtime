# Request tags

Sometimes you need to do something after a request is successfully sent or failed. I.e., you may want to get a 
message ID after it's successfully sent, and save this ID into your DB.

Request tags are intended for such purposes.

## How it works

In a route action you should define list of tags for a particular request. After the request is sent, all handlers
for the given tag list will be executed. If it's succeeded, success handler will be called, otherwise a fail handler will be called.

## Instruction and example

Here is an instruction for:
1. Inside an action - add a tag to the message into `Response`: 
    ```php
    return new Response($update)
       ->withRequest(new TelegramRequestDecorator($message, 'foo'));
    ```
    In this example we add a `foo` tag to the given message.
2. Create a tag handler. It should follow the [Extended Callable Definitions syntax](./04-extended-callable-definitions.md).
    As argument list it should take
    1. An `Update` object
    2. A `TelegramRequestDecorator` object
    3. An array containing Telegram API response
    4. It also may take as additional arguments any dependency which can be resolved by a DI Container. 
    
    If it's a handler for a single request, which is sent from a single route action, it's handy to create a new method
    inside the action as a tag handler.
    Let's create such a handler:
    ```php
    final readonly class FooHandler
    {
        public function __construct(private FooRepository $repository) {}
    
        public function handleSuccess(Update $update, TelegramRequestDecorator $request, array $response): void {
            $this->repository->saveMessageId($response['message_id']);
        }
    }
    ```
    
    You can also create a failure handler for the case when a request fails. We just don't need it in the example.  
    _**Note: for failure handlers the third argument will be of type `TelegramRequestException`, not `array`.**_
3. Connect a tag and its handler to each other. See the correct way to do this [via yiisoft/config](../getting-started/02-quick-start-yiisoft-config.md)
    or [by hand](../getting-started/03-getting-started-common.md).  
    Generally we'll add a new success handler which will look like
    ```php
    'foo' => [
        [FooHandler::class, 'handleSuccess']
    ],
    ```
