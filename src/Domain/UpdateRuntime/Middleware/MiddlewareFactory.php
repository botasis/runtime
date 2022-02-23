<?php

declare(strict_types=1);

namespace Viktorprogger\TelegramBot\Domain\UpdateRuntime\Middleware;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Viktorprogger\TelegramBot\Domain\Client\ResponseInterface;
use Viktorprogger\TelegramBot\Domain\UpdateRuntime\CallableFactory;
use Viktorprogger\TelegramBot\Domain\UpdateRuntime\RequestHandlerInterface;
use Viktorprogger\TelegramBot\Domain\UpdateRuntime\TelegramRequest;
use Yiisoft\Injector\Injector;

use function is_string;

/**
 * Creates a middleware based on the definition provided.
 */
final class MiddlewareFactory implements MiddlewareFactoryInterface
{
    /**
     * @param ContainerInterface $container Container to use for resolving definitions.
     */
    public function __construct(
        private readonly ContainerInterface $container,
        private readonly CallableFactory $callableFactory,
    ) {
    }

    /**
     * @param MiddlewareInterface|callable|array|string $middlewareDefinition Middleware definition in one of the following formats:
     *
     * - A middleware object.
     * - A name of a middleware class. The middleware instance will be obtained from container and executed.
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
     * @return MiddlewareInterface
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function create(MiddlewareInterface|callable|array|string $middlewareDefinition): MiddlewareInterface
    {
        if ($middlewareDefinition instanceof MiddlewareInterface) {
            return $middlewareDefinition;
        }

        if (is_string($middlewareDefinition) && is_subclass_of($middlewareDefinition, MiddlewareInterface::class)) {
            /** @var MiddlewareInterface */
            return $this->container->get($middlewareDefinition);
        }

        $callable = $this->callableFactory->create($middlewareDefinition);

        return $this->wrapCallable($callable);
    }

    private function wrapCallable(callable $callback): MiddlewareInterface
    {
        return new class ($callback, $this->container) implements MiddlewareInterface {
            private ContainerInterface $container;
            private $callback;

            public function __construct(callable $callback, ContainerInterface $container)
            {
                $this->callback = $callback;
                $this->container = $container;
            }

            public function process(
                TelegramRequest $request,
                RequestHandlerInterface $handler,
            ): ResponseInterface {
                $response = (new Injector($this->container))->invoke($this->callback, [$request, $handler]);
                if ($response instanceof ResponseInterface) {
                    return $response;
                }
                if ($response instanceof MiddlewareInterface) {
                    return $response->process($request, $handler);
                }

                throw new InvalidMiddlewareDefinitionException($this->callback);
            }
        };
    }
}
