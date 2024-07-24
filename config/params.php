<?php

declare(strict_types=1);

use Botasis\Runtime\Console\GetUpdatesCommand;
use Botasis\Runtime\Console\SetTelegramWebhookCommand;

return [
    'botasis/telegram-bot' => [
        'bot token' => '',
        'errors to ignore' => [],
        'response tags' => [
            'success' => [],
            'error' => [],
        ],
        'routes' => [],
    ],
    'yiisoft/yii-console' => [
        'commands' => [
            'botasis/telegram/updates' => GetUpdatesCommand::class,
            'botasis/telegram/set-webhook' => SetTelegramWebhookCommand::class,
        ],
    ],
];
