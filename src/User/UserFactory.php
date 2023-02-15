<?php

declare(strict_types=1);

namespace Viktorprogger\TelegramBot\User;

final readonly class UserFactory
{
    public function __construct(private UserIdFactoryInterface $idFactory)
    {
    }

    public function create(string $id): User
    {
        return new User($this->idFactory->create($id));
    }
}
