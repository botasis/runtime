<?php

declare(strict_types=1);

namespace Viktorprogger\TelegramBot\UpdateRuntime;

use Viktorprogger\TelegramBot\Update\Update;
use Viktorprogger\TelegramBot\UpdateRuntime\Middleware\MiddlewareDispatcher;

final readonly class Application
{
    public function __construct(
        private Emitter $emitter,
        private RequestHandlerInterface $fallbackHandler,
        private MiddlewareDispatcher $dispatcher,
    ) {
    }

    /**
     * @param Update $request
     *
     * @return void
     * @see https://core.telegram.org/bots/api#update
     */
    public function handle(Update $request): void
    {
        $response = $this->dispatcher->dispatch($request, $this->fallbackHandler);
        $this->emitter->emit($response);
    }
}
