<?php

declare(strict_types=1);

namespace Viktorprogger\TelegramBot\Domain\Entity\User;

final readonly class UserId
{
    public function __construct(public string $value)
    {
    }
}
