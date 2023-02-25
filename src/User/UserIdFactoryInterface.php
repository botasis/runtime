<?php

declare(strict_types=1);

namespace Viktorprogger\TelegramBot\User;

interface UserIdFactoryInterface
{
    public function create(string $id): UserId;
}
