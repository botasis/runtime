# Route Action

Actions are the central concept in `botasis/runtime`. Actions contain all the logic of your application. E.g. in actions
you will increase/decrease a budget in a financial bot. Requests to OpenAI API in an AI bot will be made in actions too.

What should you know about route actions?
1. Action definition should follow the [Callable Definitions Extended](../key-concepts/04-extended-callable-definitions.md) format
2. All actions **MUST** return either `ResponseInterface` or `null`/`void`. Any other return value will cause an exception.
3. In an action callable you can retrieve dynamic count of arguments. Here is the explanation:
  - `Update` - an argument of this type will always receive the current Telegram Update object
  - Attributes from an `Update` object as typed arguments via `#[UpdateAttribute]` attribute. [Read more](../key-concepts/05-update-attibute.md).
  - Dependencies from a DI container. All other dependencies which are registered in your Container will be resolved too.

Here is an example. Given we have an action definition looking like that: `[FooAction, 'handle']`. When it should be called,
an instance of `FooAction` will be created by a DI container and its `handle` method will be called.

```php
final readonly class FooAction
{
    /**
     * @param Foo $foo This parameter will be resolved via DI container on object creation
     */
    public function __construct(private Foo $foo) {}

    public function handle(
        Update $update, // a Telegram Update object for the current request from Telegram
        #[UpdateAttribute('bar')] ?string $bar, // Analog of $update->getAttribute('bar'), but strictly typed as string. Nullable, so no error will be thrown if there is no such attribute in the Update object.
        #[UpdateAttribute('my-user')] User $user, // Analog of $update->getAttribute('my-user'), but strictly typed as a User object
        Baz $baz, // a Baz object resolved by a DI container
    ): void {
        // do your stuff
    }
}
```

By the way: if `handle` were a static function, then instance of `FooAction` would not be created.
