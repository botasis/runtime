<?php

declare(strict_types=1);

namespace Botasis\Runtime;

use Botasis\Runtime\Middleware\MiddlewareDispatcher;
use Botasis\Runtime\Response\ResponseInterface;
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
     * @param Update $update
     *
     * @return ResponseInterface
     */
    public function handle(Update $update): ResponseInterface
    {
        $response = $this->dispatcher->dispatch($update, $this->fallbackHandler);
        $this->emitter->emit($response);

        return $response;
    }
}
