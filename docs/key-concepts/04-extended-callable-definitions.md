# Callable Definitions Extended

Botasis callable definitions extend [native PHP callables](https://www.php.net/manual/en/language.types.callable.php).
That means, there are two types of definitions. Nevertheless, each of them may define dependency list in their parameter
lists, which will be resolved via [yiisoft/injector](https://github.com/yiisoft/injector) and a DI Container.  
Also, the `UpdateAttribute` will be resolved.

## Type 1: Native PHP callable

When you define a callable in a such manner, they are not modified by Botasis and are called as is. As you can see
in the [PHP documentation](https://www.php.net/manual/en/language.types.callable.php), there are several ways to
define a native callable:

- **Closure (lambda function)**. It may be static. Example:
  ```php
  $callable = static function(Update $update) {
    // do stuff
  }
  ```
- **First class callable**. It's a Closure too, BTW ;) Example:
  ```php
  $callable = trim(...);
  $callable2 = $this->foo(...);
  ```
- **A class static function**. When a class has a static function, an array syntax may be used:
  ```php
  $callable = [Foo::class, 'bar']; // this will be called the same way as Foo::bar();
  ```
- **An object method**. The same as above, but with an object and a non-static method:
  ```php
  $foo = new Foo();
  $callable = [$foo, 'bar']; // this will be called the same way as $foo->bar();
  ```
- **A class static function as a string**. I don't recommend you to use this ability, as it's non-obvious and
  hard to refactor, but it still exists:
  ```php
  $callable = 'Foo::bar'; // this will be called the same way as Foo::bar();
  ```
- **A name of a named function**:
  ```php
  function foo() {
    // do stuff
  }
  $callable = 'foo';
  $callable2 = 'array_map';
  ```
- **Callable objects**. An object with [the `__invoke` method](https://www.php.net/manual/en/language.oop5.magic.php#object.invoke) implemented:
  ```php
  class Foo 
  {
    public function __invoke()
    {
      // do stuff
    }
  }
  
  $callable = new Foo();
  ```

## Type 2: Callable Definition Extension

Under the hood, this extension behaves exactly like the **Type 1** ones. But there is a major difference TOO:
all the objects are instantiated automatically with a DI Container with all their dependencies
and in a lazy way (only when they are really needed).

Let's describe the ways to define such callables:

- An object method through a class name or alias:
  ```php
  final readonly class Foo 
  {
    public function __construct(private MyHeavyDependency $dependency) {}
  
    public function bar()
    {
      // do stuff
    }
  }
  
  $callable = [Foo::class, 'bar'];
  ```
  You may imagine that such a definition will be resolved in a way like this:
  ```php
  if ($container->has($callable[0])) {
    $callable[0] = $container->get($callable[0])
  }
  
  $callable();
  ```
  It's more complicated really, but this example shows the main idea.
- Class name of an object with [the `__invoke` method](https://www.php.net/manual/en/language.oop5.magic.php#object.invoke)
  implemented:
  ```php
  $callable = Foo::class;
  ```
  It works the same way as above: an object will be retrieved from a DI container and called as a function.

_Note: you can use an alias registered in your DI Container instead of a class name._ This will also work if you have
a "class alias" definition in container:
```php
$callable = 'class alias'; // for a "callable object" 
$callable2 = ['class alias', 'foo']; // to call "foo" method of an object found by "class alias" in DI Container
```
