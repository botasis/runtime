<?php

declare(strict_types=1);

namespace Botasis\Runtime\Router\Exception;

use LogicException;
use Yiisoft\FriendlyException\FriendlyExceptionInterface;

/**
 * @internal
 */
final class InvalidRuleReturnTypeException extends LogicException implements FriendlyExceptionInterface
{
    private string $routePath;
    private string $type;

    public function __construct(mixed $value, string ...$routeKeys)
    {
        $this->routePath = implode(' => ', array_map(static fn(string $key) => "\"$key\"", $routeKeys));
        $this->type = get_debug_type($value);
        $message = "Invalid rule return type in route $this->routePath. Expected boolean, $this->type given.";

        parent::__construct($message);
    }

    public function getName(): string
    {
        return 'Invalid Route Rule Return Type';
    }

    public function getSolution(): ?string
    {
        return <<<SOLUTION
        A route rule **must** return a boolean value: either true or false. But value of type "$this->type" returned.
        Please, check return type of rule for the route $this->routePath and make sure it returns a bool.
        The best way is to set a `: bool` type hint for a return value.
        SOLUTION;
    }
}
