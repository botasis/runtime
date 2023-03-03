<?php

declare(strict_types=1);

namespace Botasis\Runtime\Entity\User;

final readonly class UserId
{
    public function __construct(public string $value)
    {
    }
}
