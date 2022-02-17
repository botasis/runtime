<?php

declare(strict_types=1);

namespace Viktorprogger\TelegramBot\Domain\Entity\User;

interface UserIdFactoryInterface
{
    public function create(string $id): UserId;
}
