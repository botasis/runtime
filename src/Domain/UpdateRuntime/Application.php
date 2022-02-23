<?php

declare(strict_types=1);

namespace Viktorprogger\TelegramBot\Domain\UpdateRuntime;

use Viktorprogger\TelegramBot\Domain\UpdateRuntime\Middleware\MiddlewareDispatcher;

final class Application
{
    public function __construct(
        private readonly Emitter $emitter,
        private readonly RequestHandlerInterface $fallbackHandler,
        private readonly MiddlewareDispatcher $dispatcher,
    ) {
    }

    /**
     * @param array $update An update entry got from Telegram
     *
     * @return void
     * @see https://core.telegram.org/bots/api#update
     *
     */
    public function handle(TelegramRequest $request): void
    {
        $response = $this->dispatcher->dispatch($request, $this->fallbackHandler);
        $this->emitter->emit($response);
    }
}
