<?php

declare(strict_types=1);

namespace Viktorprogger\TelegramBot\Domain\Entity\User;

final class User
{
    public function __construct(public readonly UserId $id)
    {
    }
}
