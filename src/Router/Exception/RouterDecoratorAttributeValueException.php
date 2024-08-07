<?php

declare(strict_types=1);

namespace Botasis\Runtime\Router\Exception;

final class RouterDecoratorAttributeValueException extends AttributeValueException
{
    public function __construct(string $callableType, AttributeValueException $previous, string ...$routeKeys)
    {
        $routePath = implode(' => ', array_map(static fn(string $key) => "\"$key\"", $routeKeys));
        $message = "An error occurred while getting an attribute value for $callableType in route $routePath: {$previous->getMessage()}";

        parent::__construct($message, previous: $previous);
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        /** @var AttributeValueException $previous */
        $previous = $this->getPrevious();

        return $previous->getName();
    }

    /**
     * @inheritDoc
     */
    public function getSolution(): ?string
    {
        /** @var AttributeValueException $previous */
        $previous = $this->getPrevious();

        return $previous->getSolution();
    }
}
