<?php

declare(strict_types=1);

use Botasis\Runtime\Event\IgnoredErrorHandler;
use Botasis\Runtime\Event\RequestErrorEvent;

return [
    RequestErrorEvent::class => [
        [IgnoredErrorHandler::class, 'handle'],
    ],
];
