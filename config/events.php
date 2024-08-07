<?php

declare(strict_types=1);

use Botasis\Runtime\Event\IgnoredErrorHandler;
use Botasis\Runtime\Event\RequestErrorEvent;
use Botasis\Runtime\Event\RequestSuccessEvent;
use Botasis\Runtime\Event\RequestTagsHandler;

return [
    RequestSuccessEvent::class => [
        [RequestTagsHandler::class, 'handleSuccess'],
    ],
    RequestErrorEvent::class => [
        [IgnoredErrorHandler::class, 'handle'],
        [RequestTagsHandler::class, 'handleError'],
    ],
];
