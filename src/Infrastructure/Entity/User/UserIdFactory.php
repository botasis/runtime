<?php

declare(strict_types=1);

namespace Viktorprogger\TelegramBot\Infrastructure\Entity\User;

use Viktorprogger\TelegramBot\Domain\Entity\User\UserId;
use Viktorprogger\TelegramBot\Domain\Entity\User\UserIdFactoryInterface;

final class UserIdFactory implements UserIdFactoryInterface
{
    public function create(string $id): UserId
    {
        return new UserId($id);
    }
}
