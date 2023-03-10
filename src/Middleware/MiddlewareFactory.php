<?php

declare(strict_types=1);

namespace Botasis\Runtime\Middleware;

use Botasis\Runtime\CallableFactory;
use Botasis\Runtime\Middleware\Exception\InvalidMiddlewareDefinitionException;
use Botasis\Runtime\Response\ResponseInterface;
use Botasis\Runtime\Update\Update;
use Botasis\Runtime\UpdateHandlerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Yiisoft\Injector\Injector;

use function is_string;

/**
 * Creates a middleware based on the definition provided.
 */
final readonly class MiddlewareFactory implements MiddlewareFactoryInterface
{
    /**
     * @param ContainerInterface $container Container to use for resolving definitions.
     */
    public function __construct(
        private ContainerInterface $container,
        private CallableFactory $callableFactory,
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
     * and {@see UpdateHandlerInterface}.
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
                Update $request,
                UpdateHandlerInterface $handler,
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
