<?php

declare(strict_types=1);

namespace Botasis\Runtime\Event;

use Botasis\Client\Telegram\Request\TelegramRequestInterface;
use Botasis\Runtime\Update\Update;
use Psr\EventDispatcher\StoppableEventInterface;

final class RequestSuccessEvent implements StoppableEventInterface
{
    public function __construct(
        public readonly TelegramRequestInterface $request,
        public readonly ?array $responseDecoded,
        public readonly Update $update,
        public bool $isPropagationStopped = false,
    ) {
    }

    public function isPropagationStopped(): bool
    {
        return $this->isPropagationStopped;
    }
}
