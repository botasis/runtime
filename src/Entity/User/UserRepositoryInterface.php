<?php

declare(strict_types=1);

namespace Viktorprogger\TelegramBot\Entity\User;

interface UserRepositoryInterface
{
    public function exists(UserId $id): bool;

    public function create(User $user): void;
}
