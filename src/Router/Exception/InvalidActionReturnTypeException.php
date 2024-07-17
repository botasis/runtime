<?php

declare(strict_types=1);

namespace Botasis\Runtime\Router\Exception;

use Botasis\Runtime\Response\ResponseInterface;
use LogicException;
use Yiisoft\FriendlyException\FriendlyExceptionInterface;

/**
 * @internal
 */
final class InvalidActionReturnTypeException extends LogicException implements FriendlyExceptionInterface
{
    private string $routePath;
    private string $type;

    public function __construct(mixed $value, string ...$routeKeys)
    {
        $this->routePath = implode(' => ', array_map(static fn(string $key) => "\"$key\"", $routeKeys));
        $this->type = get_debug_type($value);
        $responseInterface = ResponseInterface::class;
        $message = "Invalid action return type in route $this->routePath. Expected null|$responseInterface, $this->type given.";

        parent::__construct($message);
    }

    public function getName(): string
    {
        return 'Invalid Route Action Return Type';
    }

    public function getSolution(): ?string
    {
        $responseInterface = ResponseInterface::class;

        return <<<SOLUTION
        A route action **must** return either `null` or `$responseInterface` result. But value of type "$this->type" returned.
        Please, check return type of action for the route $this->routePath and make sure it returns a correct value.
        The best way is to set a type hint for a return value. It may be `null`, `void` or `$responseInterface`:
        ```php
        public function actionVoid(Update \$update): void {
          // do stuff
          return;
        }
        public function actionNull(Update \$update): null {
          // do stuff
          return null;
        }
        public function actionResponse(Update \$update): ResponseInterface {
          // do stuff
          return new \\Botasis\\Runtime\\Response\\Response(\$update);
        }
        ```
        SOLUTION;
    }
}
