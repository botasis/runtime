<?php

declare(strict_types=1);

namespace Viktorprogger\TelegramBot\Entity\User;

final readonly class UserId
{
    public function __construct(public string $value)
    {
    }
}
