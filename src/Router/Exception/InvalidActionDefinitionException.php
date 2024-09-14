<?php

declare(strict_types=1);

namespace Botasis\Runtime\Router\Exception;

use InvalidArgumentException;
use Throwable;
use Yiisoft\FriendlyException\FriendlyExceptionInterface;

/**
 * @internal
 */
final class InvalidActionDefinitionException extends InvalidArgumentException implements FriendlyExceptionInterface
{
    private string $routePath;

    public function __construct(?Throwable $previous, string ...$routeKeys)
    {
        $this->routePath = implode(' => ', array_map(static fn(string $key) => "\"$key\"", $routeKeys));
        $message = "Invalid action definition in route $this->routePath";

        parent::__construct($message, previous: $previous);
    }

    public function getName(): string
    {
        return 'Invalid Route Action Definition';
    }

    public function getSolution(): ?string
    {
        return <<<SOLUTION
            The rule definition in the route is incorrect. An errored route path (including groups) is: $this->routePath.
            A correct definition is one of:
            - A valid callable. See [PHP documentation](https://www.php.net/manual/en/language.types.callable.php) for details.
            - An array of two elements: `['className', 'methodName']`, where 'className' is an alias known by your DI container and 'methodName' is a non-static method name in that class.
            - A name of a class (a string) with the `__invoke()` method implemented. An instance will be obtained from container and executed.

            If you used one of the last two syntax variants, double-check if the class is resolvable by your DI container.

            A detailed list of all syntax variants can be found here: https://github.com/botasis/runtime/blob/master/docs/key-concepts/04-extended-callable-definitions.md

            **(DEPRECATED)** Alternatively you can use definition of an object, which implements `UpdateHandlerInterface`.
            SOLUTION;
    }
}
