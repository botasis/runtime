<?php

declare(strict_types=1);

namespace Botasis\Runtime\Router;

final class RuleDynamic
{
    /**
     * @var callable|array|string|object
     */
    private mixed $callbackDefinition;

    /**
     * Creates a dynamic route rule. Accepts a callable which should decide if a route should handle
     * the given update.
     * Example: new RuleDynamic(static fn(Update $update) => $update->chat->type === ChatType::PRIVATE)
     *
     * @param callable|array|string|object $callbackDefinition A callable that MUST return a boolean value. Allowed formats:
     *  - An object with the __invoke() method {@see https://www.php.net/manual/en/language.oop5.magic.php#object.invoke}
     *  - A name of a class with the __invoke() method. An instance will be obtained from container and executed.
     *  - A callable with any dependencies
     *  - A pair of a class name and a method name in format `[Foo::class, 'rule']`. `Foo` instance will
     *    be created by a DI container and `rule()` method will be executed.
     */
    public function __construct(callable|array|string|object $callbackDefinition)
    {
        $this->callbackDefinition = $callbackDefinition;
    }

    /**
     * @deprecated Use {@see getCallbackDefinition()} instead
     */
    public function getCallback(): callable|array|string|object
    {
        return $this->callbackDefinition;
    }

    public function getCallbackDefinition(): callable|array|string|object
    {
        return $this->callbackDefinition;
    }
}
