<?php

declare(strict_types=1);

namespace Botasis\Runtime\Event;

use Botasis\Client\Telegram\Client\Event\RequestErrorEvent;

/**
 * Treats all Telegram API errors with the given description as successful
 */
final readonly class IgnoredErrorHandler
{
    private array $ignoredErrors;

    public function __construct($ignoredErrors = [])
    {
        if (array_is_list($ignoredErrors)) {
            $ignoredErrors = array_fill_keys($ignoredErrors, true);
        }

        $this->ignoredErrors = $ignoredErrors;
    }

    public function handle(RequestErrorEvent $event): RequestErrorEvent
    {
        if (isset($this->ignoredErrors[$event->responseDecoded['description'] ?? ''])) {
            $event->handledSuccessfully = true;
        }

        return $event;
    }
}
