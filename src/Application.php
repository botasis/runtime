<?php

declare(strict_types=1);

namespace Botasis\Runtime;

use Botasis\Runtime\Middleware\MiddlewareDispatcher;
use Botasis\Runtime\Response\ResponseInterface;
use Botasis\Runtime\Update\Update;

final readonly class Application
{
    /**
     * @param Emitter $emitter
     * @param UpdateHandlerInterface $fallbackHandler An update handler which will be used to create a response
     *                                                in case no previous middleware (including Router) produced response.
     * @param MiddlewareDispatcher $dispatcher
     */
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
