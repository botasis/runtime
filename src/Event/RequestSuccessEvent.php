<?php

declare(strict_types=1);

namespace Botasis\Runtime\Event;

use Botasis\Runtime\Request\TelegramRequestDecorator;
use Botasis\Runtime\Update\Update;
use Psr\EventDispatcher\StoppableEventInterface;

final class RequestSuccessEvent implements StoppableEventInterface
{
    public function __construct(
        public readonly TelegramRequestDecorator $request,
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
