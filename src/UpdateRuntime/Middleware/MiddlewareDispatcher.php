<?php

declare(strict_types=1);

namespace Viktorprogger\TelegramBot\UpdateRuntime\Middleware;

use Closure;
use Psr\EventDispatcher\EventDispatcherInterface;
use Viktorprogger\TelegramBot\Request\TelegramRequest;
use Viktorprogger\TelegramBot\UpdateRuntime\RequestHandlerInterface;
use Viktorprogger\TelegramBot\Response\ResponseInterface;

final class MiddlewareDispatcher
{
    /**
     * Contains a middleware pipeline handler.
     *
     * @var MiddlewareStack|null The middleware stack.
     */
    private ?MiddlewareStack $stack = null;

    /**
     * @var array[]|callable[]|string[]
     */
    private array $middlewareDefinitions = [];

    public function __construct(
        private readonly MiddlewareFactoryInterface $middlewareFactory,
        private readonly ?EventDispatcherInterface $eventDispatcher,
    ) {
    }

    /**
     * Dispatch request through middleware to get response.
     *
     * @param TelegramRequest $request Request to pass to middleware.
     * @param RequestHandlerInterface $fallbackHandler Handler to use in case no middleware produced response.
     */
    public function dispatch(
        TelegramRequest $request,
        RequestHandlerInterface $fallbackHandler
    ): ResponseInterface {
        if ($this->stack === null) {
            $this->stack = new MiddlewareStack($this->buildMiddlewares(), $fallbackHandler, $this->eventDispatcher);
        }

        return $this->stack->handle($request);
    }

    /**
     * Returns new instance with middleware handlers replaced with the ones provided.
     * Last specified handler will be executed first.
     *
     * @param array[]|callable[]|string[] $middlewareDefinitions Each array element is:
     *
     * - A name of PSR-15 middleware class. The middleware instance will be obtained from container executed.
     * - A callable with `function(ServerRequestInterface $request, RequestHandlerInterface $handler):
     *     ResponseInterface` signature.
     * - A controller handler action in format `[TestController::class, 'index']`. `TestController` instance will
     *   be created and `index()` method will be executed.
     * - A function returning a middleware. The middleware returned will be executed.
     *
     * For handler action and callable
     * typed parameters are automatically injected using dependency injection container.
     * Current request and handler could be obtained by type-hinting for {@see ServerRequestInterface}
     * and {@see RequestHandlerInterface}.
     *
     * @return self
     */
    public function withMiddlewares(array $middlewareDefinitions): self
    {
        $new = clone $this;
        $new->middlewareDefinitions = array_reverse($middlewareDefinitions);

        // Fixes a memory leak.
        unset($new->stack);
        $new->stack = null;

        return $new;
    }

    /**
     * @return bool Whether there are middleware defined in the dispatcher.
     */
    public function hasMiddlewares(): bool
    {
        return $this->middlewareDefinitions !== [];
    }

    /**
     * @return Closure[]
     */
    private function buildMiddlewares(): array
    {
        $middlewares = [];
        $factory = $this->middlewareFactory;

        foreach ($this->middlewareDefinitions as $middlewareDefinition) {
            $middlewares[] = static fn(): MiddlewareInterface => $factory->create($middlewareDefinition);
        }

        return $middlewares;
    }
}
