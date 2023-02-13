<?php

declare(strict_types=1);

namespace Viktorprogger\TelegramBot\Domain\UpdateRuntime;

use Viktorprogger\TelegramBot\Domain\Entity\Request\TelegramRequest;
use Viktorprogger\TelegramBot\Domain\UpdateRuntime\Middleware\MiddlewareDispatcher;

final readonly class Application
{
    public function __construct(
        private Emitter $emitter,
        private RequestHandlerInterface $fallbackHandler,
        private MiddlewareDispatcher $dispatcher,
    ) {
    }

    /**
     * @param TelegramRequest $request
     *
     * @return void
     * @see https://core.telegram.org/bots/api#update
     */
    public function handle(TelegramRequest $request): void
    {
        $response = $this->dispatcher->dispatch($request, $this->fallbackHandler);
        $this->emitter->emit($response);
    }
}
