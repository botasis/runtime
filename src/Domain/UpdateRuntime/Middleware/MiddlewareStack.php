<?php

declare(strict_types=1);

namespace Viktorprogger\TelegramBot\Domain\UpdateRuntime\Middleware;

use Closure;
use Psr\EventDispatcher\EventDispatcherInterface;
use RuntimeException;
use Viktorprogger\TelegramBot\Domain\Client\ResponseInterface;
use Viktorprogger\TelegramBot\Domain\Entity\Request\TelegramRequest;
use Viktorprogger\TelegramBot\Domain\UpdateRuntime\Middleware\Event\AfterMiddleware;
use Viktorprogger\TelegramBot\Domain\UpdateRuntime\Middleware\Event\BeforeMiddleware;
use Viktorprogger\TelegramBot\Domain\UpdateRuntime\RequestHandlerInterface;

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

    public function handle(TelegramRequest $request): ResponseInterface
    {
        if ($this->stack === null) {
            $this->build();
        }

        /** @psalm-suppress PossiblyNullReference */
        return $this->stack->handle($request);
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

            public function handle(TelegramRequest $request): ResponseInterface
            {
                if ($this->middleware === null) {
                    $this->middleware = ($this->middlewareFactory)();
                }

                $this->dispatcher?->dispatch(new BeforeMiddleware($this->middleware, $request));

                try {
                    return $response = $this->middleware->process($request, $this->handler);
                } finally {
                    $this->dispatcher?->dispatch(new AfterMiddleware($this->middleware, $response ?? null));
                }
            }
        };
    }
}
