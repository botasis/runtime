<?php

declare(strict_types=1);

namespace Viktorprogger\TelegramBot\Infrastructure\Entity\User;

use Viktorprogger\TelegramBot\Domain\Entity\User\User;
use Viktorprogger\TelegramBot\Domain\Entity\User\UserIdFactoryInterface;

final class UserFactory
{
    public function __construct(private readonly UserIdFactoryInterface $idFactory)
    {
    }

    public function create(string $id): User
    {
        return new User($this->idFactory->create($id));
    }
}
