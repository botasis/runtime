<?php

declare(strict_types=1);

use Botasis\Runtime\Client\TelegramClientInterface;
use Botasis\Runtime\Client\TelegramClientPsr;
use Botasis\Runtime\UpdateRuntime\Middleware\MiddlewareFactory;
use Botasis\Runtime\UpdateRuntime\Middleware\MiddlewareFactoryInterface;
use Botasis\Runtime\User\SimpleUserIdFactory;
use Botasis\Runtime\User\UserIdFactoryInterface;

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
