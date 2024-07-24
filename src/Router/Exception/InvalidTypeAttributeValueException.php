<?php

declare(strict_types=1);

namespace Botasis\Runtime\Router\Exception;

use Throwable;

/**
 * @internal
 */
final class InvalidTypeAttributeValueException extends AttributeValueException
{
    public function __construct(
        private readonly string $attributeName,
        private readonly string $currentType,
        private readonly string $expectedType,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            "Required attribute value $attributeName has a wrong type \"$currentType\". Expected $expectedType.",
            previous: $previous,
        );
    }

    public function getName(): string
    {
        return 'Invalid Telegram Update attribute type';
    }

    public function getSolution(): ?string
    {
        return <<<SOLUTION
            The attribute $this->attributeName has a wrong type "$this->currentType". Expected type "$this->expectedType".

            There is such a code somewhere in your app: `\$update->withAttribute('$this->attributeName', \$value)`,
            and value is of type "$this->currentType". On the other hand, a callable wants a value of type "$this->expectedType".

            Possible solutions:
            - Find an appropriate call of `\$update->withAttribute()` and change value type to "$this->expectedType"
            - Change the type of the parameter in a callable to "$this->expectedType"
            SOLUTION;
    }
}
