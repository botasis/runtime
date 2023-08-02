<?php

declare(strict_types=1);

use Botasis\Client\Telegram\Client\Event\RequestErrorEvent;
use Botasis\Runtime\Event\IgnoredErrorHandler;

return [
    RequestErrorEvent::class => [
        [IgnoredErrorHandler::class, 'handle'],
    ],
];
