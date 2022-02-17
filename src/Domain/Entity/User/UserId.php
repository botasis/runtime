<?php

declare(strict_types=1);

namespace Viktorprogger\TelegramBot\Domain\Entity\User;

final class UserId
{
    public function __construct(public readonly string $value)
    {
    }
}
