<?php

declare(strict_types=1);

namespace Botasis\Runtime;

use Botasis\Runtime\Middleware\MiddlewareDispatcher;
use Botasis\Runtime\Update\Update;

final readonly class Application
{
    public function __construct(
        private Emitter $emitter,
        private UpdateHandlerInterface $fallbackHandler,
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
