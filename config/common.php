<?php

declare(strict_types=1);

/**
 * @var array $params
 */

use Botasis\Client\Telegram\Client\ClientInterface;
use Botasis\Client\Telegram\Client\ClientPsr;
use Botasis\Runtime\Middleware\MiddlewareFactory;
use Botasis\Runtime\Middleware\MiddlewareFactoryInterface;

return [
    MiddlewareFactoryInterface::class => MiddlewareFactory::class,
    ClientInterface::class => [
        'class' => ClientPsr::class,
        '__construct()' => [
            'token' => $params['viktorprogger/telegram-bot']['bot token'],
            'errorsToIgnore' => $params['viktorprogger/telegram-bot']['errors to ignore'],
        ],
    ],
];
