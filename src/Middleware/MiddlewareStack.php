<?php

declare(strict_types=1);

namespace Botasis\Runtime\Middleware;

use Botasis\Runtime\Middleware\Event\AfterMiddleware;
use Botasis\Runtime\Middleware\Event\BeforeMiddleware;
use Botasis\Runtime\Response\ResponseInterface;
use Botasis\Runtime\Update\Update;
use Botasis\Runtime\UpdateHandlerInterface;
use Closure;
use Psr\EventDispatcher\EventDispatcherInterface;
use RuntimeException;

final class MiddlewareStack implements UpdateHandlerInterface
{
    /**
     * Contains a stack of middleware wrapped in handlers.
     * Each handler points to the handler of middleware that will be processed next.
     *
     * @var UpdateHandlerInterface|null stack of middleware
     */
    private ?UpdateHandlerInterface $stack = null;

    /**
     * @param Closure[] $middlewares Middlewares.
     * @param UpdateHandlerInterface $fallbackHandler Fallback handler
     * @param EventDispatcherInterface|null $dispatcher Event dispatcher to use for triggering before/after middleware
     * events.
     */
    public function __construct(
        private readonly array $middlewares,
        private readonly UpdateHandlerInterface $fallbackHandler,
        private readonly ?EventDispatcherInterface $dispatcher = null,
    ) {
        if ($middlewares === []) {
            throw new RuntimeException('Stack is empty.');
        }
    }

    public function handle(Update $update): ResponseInterface
    {
        if ($this->stack === null) {
            $this->build();
        }

        /** @psalm-suppress PossiblyNullReference */
        return $this->stack->handle($update);
    }

    private function build(): void
    {
        $handler = $this->fallbackHandler;

        foreach ($this->middlewares as $middleware) {
            $handler = $this->wrap($middleware, $handler);
        }

        $this->stack = $handler;
    }

    /**
     * Wrap handler by middlewares.
     */
    private function wrap(Closure $middlewareFactory, UpdateHandlerInterface $handler): UpdateHandlerInterface
    {
        return new class ($middlewareFactory, $handler, $this->dispatcher) implements UpdateHandlerInterface {
            private ?MiddlewareInterface $middleware = null;

            public function __construct(
                private readonly Closure $middlewareFactory,
                private readonly UpdateHandlerInterface $handler,
                private readonly ?EventDispatcherInterface $dispatcher
            ) {
            }

            public function handle(Update $update): ResponseInterface
            {
                if ($this->middleware === null) {
                    $this->middleware = ($this->middlewareFactory)();
                }

                $this->dispatcher?->dispatch(new BeforeMiddleware($this->middleware, $update));

                try {
                    return $response = $this->middleware->process($update, $this->handler);
                } finally {
                    $this->dispatcher?->dispatch(new AfterMiddleware($this->middleware, $response ?? null));
                }
            }
        };
    }
}
