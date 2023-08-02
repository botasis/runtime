<?php

declare(strict_types=1);

namespace Botasis\Runtime\Event;

use Botasis\Client\Telegram\Client\Exception\TelegramRequestException;
use Botasis\Client\Telegram\Request\TelegramRequestInterface;
use Botasis\Runtime\Update\Update;
use Psr\EventDispatcher\StoppableEventInterface;

final class RequestErrorEvent implements StoppableEventInterface
{
    /**
     * @param bool $suppressException Set this to true if you don't want an exception to be thrown
     * @param bool $isPropagationStopped Set this to true to stop propagation of the event.
     */
    public function __construct(
        public readonly TelegramRequestInterface $request,
        public readonly TelegramRequestException $exception,
        public readonly Update $update,
        public bool $suppressException = false,
        public bool $isPropagationStopped = false,
    ) {
    }

    public function isPropagationStopped(): bool
    {
        return $this->isPropagationStopped;
    }
}
