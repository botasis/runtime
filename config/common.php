<?php

declare(strict_types=1);

use Viktorprogger\TelegramBot\Domain\Client\TelegramClientInterface;
use Viktorprogger\TelegramBot\Domain\Entity\Request\RequestRepositoryInterface;
use Viktorprogger\TelegramBot\Domain\Entity\User\UserIdFactoryInterface;
use Viktorprogger\TelegramBot\Domain\Entity\User\UserRepositoryInterface;
use Viktorprogger\TelegramBot\Domain\UpdateRuntime\Middleware\MiddlewareFactory;
use Viktorprogger\TelegramBot\Domain\UpdateRuntime\Middleware\MiddlewareFactoryInterface;
use Viktorprogger\TelegramBot\Infrastructure\Client\TelegramClientPsr;
use Viktorprogger\TelegramBot\Infrastructure\Entity\Request\Cycle\RequestRepository;
use Viktorprogger\TelegramBot\Infrastructure\Entity\User\Cycle\UserRepository;
use Viktorprogger\TelegramBot\Infrastructure\Entity\User\UserIdFactory;

/**
 * @var array $params
 */

return [
    UserIdFactoryInterface::class => UserIdFactory::class,
    UserRepositoryInterface::class => UserRepository::class,
    RequestRepositoryInterface::class => RequestRepository::class,
    MiddlewareFactoryInterface::class => MiddlewareFactory::class,
    TelegramClientInterface::class => [
        'class' => TelegramClientPsr::class,
        '__construct()' => [
            'token' => $params['viktorprogger/telegram-bot']['bot token'],
            'errorsToIgnore' => $params['viktorprogger/telegram-bot']['errors to ignore'],
        ],
    ],
];
