<?php

declare(strict_types=1);

use Viktorprogger\TelegramBot\Infrastructure\Console\GetUpdatesCommand;
use Viktorprogger\TelegramBot\Infrastructure\Console\SetTelegramWebhookCommand;
use Viktorprogger\TelegramBot\Infrastructure\TelegramHookHandler;

return [
    'yiisoft/yii-console' => [
        'commands' => [
            'viktorprogger/telegram/updates' => GetUpdatesCommand::class,
            'viktorprogger/telegram/set-webhook' => SetTelegramWebhookCommand::class,
        ],
    ],
    'yiisoft/queue' => [
        'handlers' => [
            TelegramHookHandler::NAME => TelegramHookHandler::class,
        ],
    ],
];
