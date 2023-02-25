<?php

declare(strict_types=1);

use Viktorprogger\TelegramBot\Console\GetUpdatesCommand;
use Viktorprogger\TelegramBot\Console\SetTelegramWebhookCommand;

return [
    'viktorprogger/telegram-bot' => [
        'bot token' => '',
        'errors to ignore' => [],
    ],
    'yiisoft/yii-console' => [
        'commands' => [
            'viktorprogger/telegram/updates' => GetUpdatesCommand::class,
            'viktorprogger/telegram/set-webhook' => SetTelegramWebhookCommand::class,
        ],
    ],
];
