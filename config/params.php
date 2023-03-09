<?php

declare(strict_types=1);

use Botasis\Runtime\Console\GetUpdatesCommand;
use Botasis\Runtime\Console\SetTelegramWebhookCommand;

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
