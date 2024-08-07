<?php

declare(strict_types=1);

namespace Botasis\Runtime\Router;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER)]
final class UpdateAttribute
{
    public function __construct(public readonly string $name) {}
}
