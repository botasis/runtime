<?php

declare(strict_types=1);

namespace Botasis\Runtime\Event;

/**
 * Treats all Telegram API errors with the given description as successful
 */
final readonly class IgnoredErrorHandler
{
    private array $ignoredErrors;

    /**
     * @param list<string> $ignoredErrors List of telegram error messages to ignore
     */
    public function __construct(string ...$ignoredErrors)
    {
        if (array_is_list($ignoredErrors)) {
            $ignoredErrors = array_fill_keys($ignoredErrors, true);
        }

        $this->ignoredErrors = $ignoredErrors;
    }

    public function handle(RequestErrorEvent $event): RequestErrorEvent
    {
        if (isset($this->ignoredErrors[$event->exception->responseDecoded['description'] ?? ''])) {
            $event->suppressException = true;
        }

        return $event;
    }
}
