<?php

declare(strict_types=1);

use Viktorprogger\TelegramBot\Infrastructure\Console\GetUpdatesCommand;
use Viktorprogger\TelegramBot\Infrastructure\Console\SetTelegramWebhookCommand;

return [
    'yiisoft/yii-console' => [
        'commands' => [
            'viktorprogger/telegram/updates' => GetUpdatesCommand::class,
            'viktorprogger/telegram/set-webhook' => SetTelegramWebhookCommand::class,
        ],
    ],
];
