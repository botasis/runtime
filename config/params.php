<?php

declare(strict_types=1);

use Botasis\Runtime\Console\GetUpdatesCommand;
use Botasis\Runtime\Console\SetTelegramWebhookCommand;
use Botasis\Runtime\Handler\DummyUpdateHandler;

return [
    'botasis/runtime' => [
        'bot token' => '',
        'errors to ignore' => [],
        'fallback handler' => DummyUpdateHandler::class,
        'request tags' => [
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
