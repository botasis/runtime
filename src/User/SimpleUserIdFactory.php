<?php

declare(strict_types=1);

namespace Viktorprogger\TelegramBot\User;

use Viktorprogger\TelegramBot\User\UserId;
use Viktorprogger\TelegramBot\User\UserIdFactoryInterface;

final class SimpleUserIdFactory implements UserIdFactoryInterface
{
    public function create(string $id): UserId
    {
        return new UserId($id);
    }
}
