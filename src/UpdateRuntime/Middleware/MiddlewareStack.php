<?php

declare(strict_types=1);

namespace Botasis\Runtime\UpdateRuntime\Middleware;

use Closure;
use Psr\EventDispatcher\EventDispatcherInterface;
use RuntimeException;
use Botasis\Runtime\Update\Update;
use Botasis\Runtime\UpdateRuntime\Middleware\Event\AfterMiddleware;
use Botasis\Runtime\UpdateRuntime\Middleware\Event\BeforeMiddleware;
use Botasis\Runtime\UpdateRuntime\RequestHandlerInterface;
use Botasis\Runtime\Response\ResponseInterface;

final class MiddlewareStack implements RequestHandlerInterface
{
    /**
     * Contains a stack of middleware wrapped in handlers.
     * Each handler points to the handler of middleware that will be processed next.
     *
     * @var RequestHandlerInterface|null stack of middleware
     */
    private ?RequestHandlerInterface $stack = null;

    /**
     * @param Closure[] $middlewares Middlewares.
     * @param RequestHandlerInterface $fallbackHandler Fallback handler
     * @param EventDispatcherInterface|null $dispatcher Event dispatcher to use for triggering before/after middleware
     * events.
     */
    public function __construct(
        private readonly array $middlewares,
        private readonly RequestHandlerInterface $fallbackHandler,
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
    private function wrap(Closure $middlewareFactory, RequestHandlerInterface $handler): RequestHandlerInterface
    {
        return new class ($middlewareFactory, $handler, $this->dispatcher) implements RequestHandlerInterface {
            private ?MiddlewareInterface $middleware = null;

            public function __construct(
                private readonly Closure $middlewareFactory,
                private readonly RequestHandlerInterface $handler,
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
