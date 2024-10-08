<?php

namespace Botasis\Runtime\Router;

use Botasis\Runtime\InvalidCallableConfigurationException;
use Botasis\Runtime\Middleware\MiddlewareDispatcher;
use Botasis\Runtime\Response\Response;
use Botasis\Runtime\Response\ResponseInterface;
use Botasis\Runtime\Router\Exception\AttributeValueException;
use Botasis\Runtime\Router\Exception\InvalidActionDefinitionException;
use Botasis\Runtime\Router\Exception\InvalidActionReturnTypeException;
use Botasis\Runtime\Router\Exception\InvalidRuleDefinitionException;
use Botasis\Runtime\Router\Exception\InvalidRuleReturnTypeException;
use Botasis\Runtime\Router\Exception\RouterDecoratorAttributeValueException;
use Botasis\Runtime\Update\Update;
use Botasis\Runtime\UpdateHandlerInterface;
use Closure;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use RuntimeException;

final class Router
{
    /** @var array<Group<RuleDynamic>|Route<RuleDynamic>> */
    private readonly array $routes;
    /** @var array<mixed, array{key: mixed, route: Group<RuleStatic>|Route<RuleStatic>}> */
    private readonly array $routesStatic;
    private array $compiled = [];
    private array $compiledStatic = [];
    /** @var array<callable(update):bool> */
    private array $compiledRules = [];
    private ?UpdateHandlerInterface $emptyFallbackHandler = null;

    /** @var string[] */
    private array $routeKeys = [];

    /**
     * @psalm-param $routes array<Group|Route>
     */
    public function __construct(
        private readonly ContainerInterface $container,
        private readonly MiddlewareDispatcher $middlewareDispatcher,
        private readonly CallableResolver $callableResolver,
        Group|Route ...$routes,
    ) {
        $routesDynamic = $routesStatic = [];

        foreach ($routes as $key => $route) {
            if ($route->rule instanceof RuleStatic) {
                /** @psalm-var Route<RuleStatic>|Group<RuleStatic> $route */
                $routesStatic[$route->rule->message] = ['key' => $key, 'route' => $route];
            } else {
                /** @psalm-var Route<RuleDynamic>|Group<RuleDynamic> $route */
                $routesDynamic[$key] = $route;
            }
        }

        $this->routes = $routesDynamic;
        $this->routesStatic = $routesStatic;
    }

    public function match(Update $update): UpdateHandlerInterface
    {
        ['key' => $key, 'route' => $route] = $this->routesStatic[$update->requestData] ?? ['key' => null, 'route' => null];
        if ($route !== null) {
            if (!isset($this->compiledStatic[$route->rule->message])) {
                $this->compiledStatic[$route->rule->message] = $this->compileRoute($route, (string) $key);
            }

            return $this->compiledStatic[$route->rule->message];
        }

        foreach ($this->routes as $key => $route) {
            if (!isset($this->compiledRules[$key])) {
                try {
                    $this->compiledRules[$key] = $this->callableResolver->resolve(
                        $route->rule->getCallbackDefinition(),
                    );
                } catch (ContainerExceptionInterface|InvalidCallableConfigurationException $e) {
                    throw new InvalidRuleDefinitionException($e, ...$this->routeKeys);
                }
            }
            $rule = $this->compiledRules[$key];

            try {
                $checkResult = $rule($update);
            } catch (AttributeValueException $exception) {
                throw new RouterDecoratorAttributeValueException('rule', $exception, ...[...$this->routeKeys, $key]);
            }

            if (!is_bool($checkResult)) {
                throw new InvalidRuleReturnTypeException($checkResult, ...[...$this->routeKeys, $key]);
            }

            if ($checkResult) {
                if (!isset($this->compiled[$key])) {
                    $this->compiled[$key] = $this->compileRoute($route, (string) $key);
                }

                return $this->compiled[$key];
            }
        }

        throw new NotFoundException($update);
    }

    private function compileRoute(Route|Group $route, string $routeKey): UpdateHandlerInterface
    {
        $middlewares = $route->getMiddlewares();
        $middlewares[] = $this->getActionWrapped($route, $routeKey);
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
    private function getActionWrapped(Route|Group $route, string $routeKey): Closure
    {
        if ($route instanceof Group) {
            $router = new self(
                $this->container,
                $this->middlewareDispatcher,
                $this->callableResolver,
                ...$route->routes,
            );
            $router->setRouteKeys(...[...$this->routeKeys, $routeKey]);

            return static fn(Update $update, UpdateHandlerInterface $handler): ResponseInterface => $router
                ->match($update)
                ->handle($update);
        }

        return function(Update $update, UpdateHandlerInterface $handler) use($route, $routeKey): ResponseInterface {
            try {
                $result = $this->resolveAction($route, $routeKey)($update);
            } catch (AttributeValueException $exception) {
                throw new RouterDecoratorAttributeValueException('action', $exception, ...[...$this->routeKeys, $routeKey]);
            }

            if ($result === null) {
                $result = new Response($update);
            }

            if (!$result instanceof ResponseInterface) {
                throw new InvalidActionReturnTypeException($result, ...[...$this->routeKeys, $routeKey]);
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
    private function resolveAction(Route $route, string $routeKey): Closure
    {
        if ($route->action instanceof UpdateHandlerInterface) {
            trigger_deprecation('botasis/runtime', '0.12.0', 'Route actions implementing UpdateHandlerInterface are deprecated. Use Extended Callables syntax instead.');

            return [$route->action, 'handle'](...);
        }

        try {
            return $this->callableResolver->resolve($route->action);
        } catch (InvalidCallableConfigurationException $exception) {
            if (is_string($route->action) && $this->container->has($route->action)) {
                $action = $this->container->get($route->action);
                if ($action instanceof UpdateHandlerInterface) {
                    trigger_deprecation('botasis/runtime', '0.12.0', 'Route actions implementing UpdateHandlerInterface are deprecated. Use Extended Callables syntax instead.');

                    return [$action, 'handle'](...);
                }
            }

            throw new InvalidActionDefinitionException(
                $exception,
                ...[...$this->routeKeys, $routeKey],
            );
        }
    }

    private function setRouteKeys(string ...$keys): void {
        $this->routeKeys = $keys;
    }
}
