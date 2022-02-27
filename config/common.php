<?php

declare(strict_types=1);

use Viktorprogger\TelegramBot\Domain\Entity\Request\RequestRepositoryInterface;
use Viktorprogger\TelegramBot\Domain\Entity\User\UserIdFactoryInterface;
use Viktorprogger\TelegramBot\Domain\Entity\User\UserRepositoryInterface;
use Viktorprogger\TelegramBot\Domain\UpdateRuntime\Middleware\MiddlewareFactory;
use Viktorprogger\TelegramBot\Domain\UpdateRuntime\Middleware\MiddlewareFactoryInterface;
use Viktorprogger\TelegramBot\Infrastructure\Entity\Request\Cycle\RequestRepository;
use Viktorprogger\TelegramBot\Infrastructure\Entity\User\Cycle\UserRepository;
use Viktorprogger\TelegramBot\Infrastructure\Entity\User\UserIdFactory;

return [
    UserIdFactoryInterface::class => UserIdFactory::class,
    UserRepositoryInterface::class => UserRepository::class,
    RequestRepositoryInterface::class => RequestRepository::class,
    MiddlewareFactoryInterface::class => MiddlewareFactory::class,
];
