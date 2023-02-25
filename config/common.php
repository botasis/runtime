<?php

declare(strict_types=1);

use Viktorprogger\TelegramBot\Client\TelegramClientInterface;
use Viktorprogger\TelegramBot\Client\TelegramClientPsr;
use Viktorprogger\TelegramBot\UpdateRuntime\Middleware\MiddlewareFactory;
use Viktorprogger\TelegramBot\UpdateRuntime\Middleware\MiddlewareFactoryInterface;
use Viktorprogger\TelegramBot\User\SimpleUserIdFactory;
use Viktorprogger\TelegramBot\User\UserIdFactoryInterface;

/**
 * @var array $params
 */

return [
    UserIdFactoryInterface::class => SimpleUserIdFactory::class,
    MiddlewareFactoryInterface::class => MiddlewareFactory::class,
    TelegramClientInterface::class => [
        'class' => TelegramClientPsr::class,
        '__construct()' => [
            'token' => $params['viktorprogger/telegram-bot']['bot token'],
            'errorsToIgnore' => $params['viktorprogger/telegram-bot']['errors to ignore'],
        ],
    ],
];
