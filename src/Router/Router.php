<?php

namespace Botasis\Runtime\Router;

use Botasis\Runtime\InvalidCallableConfigurationException;
use Botasis\Runtime\Middleware\MiddlewareDispatcher;
use Botasis\Runtime\Response\Response;
use Botasis\Runtime\Response\ResponseInterface;
use Botasis\Runtime\Update\Update;
use Botasis\Runtime\UpdateHandlerInterface;
use Closure;
use Psr\Container\ContainerInterface;
use RuntimeException;

final class Router
{
    /** @var array<Group|Route> */
    private array $routes = [];
    /** @var array<Group|Route> */
    private array $rulesStatic;
    private array $compiled = [];
    private array $compiledStatic = [];
    /** @var array<callable(update):bool> */
    private array $compiledRules = [];
    private ?UpdateHandlerInterface $emptyFallbackHandler = null;

    /**
     * @psalm-param $routes array<Group|Route>
     */
    public function __construct(
        private readonly ContainerInterface $container,
        private readonly MiddlewareDispatcher $middlewareDispatcher,
        private readonly CallableResolver $callableResolver,
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
            /** @psalm-suppress UndefinedPropertyFetch The rule property is always a RuleStatic */
            if (!isset($this->compiledStatic[$route->rule->message])) {
                $this->compiledStatic[$route->rule->message] = $this->compileRoute($route);
            }

            return $this->compiledStatic[$route->rule->message];
        }

        foreach ($this->routes as $key => $route) {
            if (!isset($this->compiledRules[$key])) {
                // TODO domain exception with explanation (use yiisoft friendly exceptions) on callable not created
                /** @psalm-suppress PossiblyUndefinedMethod The rule property is always a RuleDynamic */
                $this->compiledRules[$key] = $this->callableResolver->resolve($route->rule->getCallbackDefinition());
            }
            $rule = $this->compiledRules[$key];

            $checkResult = $rule($update);
            if (!is_bool($checkResult)) {
                // TODO domain exception with explanation (use yiisoft friendly exceptions)
                throw new RuntimeException();
            }

            if ($checkResult) {
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

    /**
     * @param Route|Group $route
     * @return Closure(Update, UpdateHandlerInterface):ResponseInterface
     */
    private function getActionWrapped(Route|Group $route): Closure
    {
        if ($route instanceof Group) {
            $router = new self(
                $this->container,
                $this->middlewareDispatcher,
                $this->callableResolver,
                ...$route->routes,
            );

            return static fn(Update $update, UpdateHandlerInterface $handler): ResponseInterface => $router
                ->match($update)
                ->handle($update);
        }

        return function(Update $update, UpdateHandlerInterface $handler) use($route): ResponseInterface {
            $result = $this->resolveAction($route)($update);
            if ($result === null) {
                $result = new Response($update);
            }

            if (!$result instanceof ResponseInterface) {
                // TODO domain exception with explanation (use yiisoft friendly exceptions)
                throw new RuntimeException('Action must return either ResponseInterface or null.');
            }

            return $result;
        };
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

    /**
     * @param Route $route
     * @return Closure(Update):mixed
     */
    private function resolveAction(Route $route): Closure
    {
        // TODO add tests for extended callables and update handlers as actions
        // TODO also test exception text with route name chains
        if ($route->action instanceof UpdateHandlerInterface) {
            return [$route->action, 'handle'](...);
        }

        try {
            return $this->callableResolver->resolve($route->action);
        } catch (InvalidCallableConfigurationException $exception) {
            if (is_string($route->action) && $this->container->has($route->action)) {
                $action = $this->container->get($route->action);
                if ($action instanceof UpdateHandlerInterface) {
                    return [$action, 'handle'](...);
                }
            }

            // TODO domain exception with explanation (use yiisoft friendly exceptions)
            throw new RuntimeException(
                'Action must be an UpdateHandlerInterface or a callable definition.',
                previous: $exception,
            );
        }
    }
}
