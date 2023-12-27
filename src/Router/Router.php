<?php

namespace Botasis\Runtime\Router;

use Botasis\Runtime\Middleware\MiddlewareDispatcher;
use Botasis\Runtime\Response\Response;
use Botasis\Runtime\Response\ResponseInterface;
use Botasis\Runtime\Update\Update;
use Botasis\Runtime\UpdateHandlerInterface;
use Closure;
use Psr\Container\ContainerInterface;

final class Router
{
    /** @var array<Group|Route> */
    private array $routes = [];
    /** @var array<Group|Route> */
    private array $rulesStatic;
    private array $compiled = [];
    private array $compiledStatic = [];
    private ?UpdateHandlerInterface $emptyFallbackHandler = null;


    /**
     * @psalm-param $routes array<Group|Route>
     */
    public function __construct(
        private readonly ContainerInterface $container,
        private readonly MiddlewareDispatcher $middlewareDispatcher,
        Group|Route ...$routes,
    ) {
        $rulesStatic = [];

        foreach ($routes as $key => $route) {
            if ($route->rule instanceof RuleStatic) {
                $rulesStatic[$route->rule->message] = $route;
            } else {
                $this->routes[$key] = $route;
            }
        }

        $this->rulesStatic = $rulesStatic;
    }

    public function match(Update $update): UpdateHandlerInterface
    {
        $route = $this->rulesStatic[$update->requestData] ?? null;
        if ($route !== null) {
            if (!isset($this->compiledStatic[$route->rule->message])) {
                $this->compiledStatic[$route->rule->message] = $this->compileRoute($route);
            }

            return $this->compiledStatic[$route->rule->message];
        }

        foreach ($this->routes as $key => $route) {
            /** @psalm-suppress PossiblyUndefinedMethod The rule property is always RuleDynamic here */
            if ($route->rule->getCallback()($update)) {
                if (!isset($this->compiled[$key])) {
                    $this->compiled[$key] = $this->compileRoute($route);
                }

                return $this->compiled[$key];
            }
        }

        throw new NotFoundException($update);
    }

    private function compileRoute(Route|Group $route): UpdateHandlerInterface
    {
        $middlewares = $route->getMiddlewares();
        $middlewares[] = $this->getActionWrapped($route);
        $dispatcher = $this->middlewareDispatcher->withMiddlewares(...$middlewares);

        return new class ($dispatcher, $this->getEmptyFallbackHandler()) implements UpdateHandlerInterface {
            public function __construct(
                private readonly MiddlewareDispatcher $dispatcher,
                private readonly UpdateHandlerInterface $fallbackHandler,
            ) {
            }

            public function handle(Update $update): ResponseInterface
            {
                return $this->dispatcher->dispatch($update, $this->fallbackHandler);
            }
        };
    }

    private function getActionWrapped(Route|Group $route): Closure
    {
        if ($route instanceof Group) {
            $router = new self(
                $this->container,
                $this->middlewareDispatcher,
                ...$route->routes,
            );

            return static fn(Update $update, UpdateHandlerInterface $handler): ResponseInterface => $router
                ->match($update)
                ->handle($update);
        }

        if (is_string($route->action)) {
            /** @psalm-suppress PossiblyInvalidArgument Action is a string */
            return fn(Update $update, UpdateHandlerInterface $handler): ResponseInterface => $this
                ->container
                ->get($route->action)
                ->handle($update);
        }

        /** @psalm-suppress PossiblyInvalidMethodCall It's always an object because the string case is checked above */
        return static fn(Update $update, UpdateHandlerInterface $handler): ResponseInterface => $route->action->handle($update);
    }

    private function getEmptyFallbackHandler(): UpdateHandlerInterface
    {
        if ($this->emptyFallbackHandler === null) {
            $this->emptyFallbackHandler = new class implements UpdateHandlerInterface {
                public function handle(Update $update): ResponseInterface
                {
                    return new Response($update);
                }
            };
        }

        return $this->emptyFallbackHandler;
    }
}
