<?php

declare(strict_types=1);

use Viktorprogger\TelegramBot\Domain\Entity\User\UserIdFactoryInterface;
use Viktorprogger\TelegramBot\Domain\Entity\User\UserRepositoryInterface;
use Viktorprogger\TelegramBot\Infrastructure\Entity\User\Cycle\UserRepository;
use Viktorprogger\TelegramBot\Infrastructure\Entity\User\UserIdFactory;

return [
    UserIdFactoryInterface::class => UserIdFactory::class,
    UserRepositoryInterface::class => UserRepository::class,
];
