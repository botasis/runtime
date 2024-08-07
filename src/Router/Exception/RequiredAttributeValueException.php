<?php

declare(strict_types=1);

namespace Botasis\Runtime\Router\Exception;

use Throwable;

/**
 * @internal
 */
final class RequiredAttributeValueException extends AttributeValueException
{
    public function __construct(
        private readonly string $attributeName,
        private readonly string $attributeType,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            "Required attribute value \"$attributeName\" is null or not set. Expected \"$attributeType\".",
            previous: $previous,
        );
    }

    public function getName(): string
    {
        return 'A required Telegram Update attribute value is null or not set';
    }

    public function getSolution(): ?string
    {
        return <<<SOLUTION
        An attribute "$this->attributeName" from a Telegram update object is required and should be of type `$this->attributeType`.
        Either no value was set or it was set to `null`.

        Possible causes:
        - A middleware, which sets a required attribute, is missing in the call stack
        - That middleware either sets `null` or doesn't call `\$update->withAttribute('$this->attributeName', \$value)`
        - A middleware doesn't take in attention that `withAttribute()` method is immutable.
            Wrong way:
            ```php
            \$update->withAttribute('$this->attributeName', \$value); // withAttribute returns a NEW Update object
            return \$update; // An old \$update object is returned, the attribute is not set
            ```
            Right way:
            ```php
            return \$update->withAttribute('$this->attributeName', \$value);
            ```
        - A `withoutAttribute()` method is called somewhere else: `\$update->withoutAttribute('$this->attributeName')`
        SOLUTION;
    }
}
