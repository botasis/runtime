<?php

declare(strict_types=1);

namespace Botasis\Runtime\Update;

final readonly class UpdateId
{
    public function __construct(public int $value)
    {
    }
}
