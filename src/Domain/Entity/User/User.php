<?php

declare(strict_types=1);

namespace Viktorprogger\TelegramBot\Domain\Entity\User;

final readonly class User
{
    public function __construct(public UserId $id)
    {
    }
}
