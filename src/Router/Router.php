<?php

namespace Botasis\Runtime\Router;

use Botasis\Runtime\Middleware\MiddlewareDispatcher;
use Botasis\Runtime\Response\Response;
use Botasis\Runtime\Response\ResponseInterface;
use Botasis\Runtime\Update\Update;
use Botasis\Runtime\UpdateHandlerInterface;
use Closure;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;

final class Router
{
    public const ROUTE_KEY_RULE_STATIC = 'rule_static';
    public const ROUTE_KEY_RULE = 'rule';
    public const ROUTE_KEY_ACTION = 'action';
    public const ROUTE_KEY_ROUTES_LIST = 'routes';
    public const ROUTE_KEY_MIDDLEWARES = 'middlewares';
    private readonly array $rulesStatic;
    private array $compiled = [];
    private ?UpdateHandlerInterface $emptyFallbackHandler = null;

    /**
     * @psalm-param $routes list<array{rule: callable, action: class-string<UpdateHandlerInterface>}>
     */
    public function __construct(
        private ContainerInterface $container,
        private MiddlewareDispatcher $middlewareDispatcher,
        private array $routes,
    ) {
        $rulesStatic = [];
        foreach ($this->routes as $key => &$route) {
            $isGroup = ($route[self::ROUTE_KEY_ROUTES_LIST] ?? []) !== [];
            if (!$isGroup xor isset($route[self::ROUTE_KEY_ACTION])) {
                throw new InvalidArgumentException('Telegram route must have either "action" or "routes" key.');
            }

            if ($isGroup) {
                $route[self::ROUTE_KEY_ACTION] = new self(
                    $this->container,
                    $this->middlewareDispatcher,
                    $route[self::ROUTE_KEY_ROUTES_LIST],
                );
            }

            $staticRule = $this->getRuleStatic($route);
            $hasStaticRule = $staticRule !== null;
            if (!$hasStaticRule xor isset($route[self::ROUTE_KEY_RULE])) {
                throw new InvalidArgumentException('Telegram route must have either "rule_static" or "rule" key.');
            }

            if ($hasStaticRule) {
                $rulesStatic[$staticRule] = $key;
            }
        }
        unset($route);

        $this->rulesStatic = $rulesStatic;
    }

    public function match(Update $update): UpdateHandlerInterface
    {
        $route = $this->rulesStatic[$update->requestData] ?? null;

        if ($route === null) {
            foreach ($this->routes as $key => $routeConfig) {
                if (isset($routeConfig[self::ROUTE_KEY_RULE]) && $routeConfig[self::ROUTE_KEY_RULE]($update)) {
                    $route = $key;

                    break;
                }
            }
        }

        if ($route === null) {
            throw new NotFoundException($update);
        }

        if (!isset($this->compiled[$route])) {
            $this->compiled[$route] = $this->compileRoute($this->routes[$route], $update);
        }

        return $this->compiled[$route];
    }

    private function getRuleStatic(array $route): ?string
    {
        if (isset($route[self::ROUTE_KEY_RULE_STATIC])) {
            $rule = $route[self::ROUTE_KEY_RULE_STATIC];
            if (is_string($rule) && $rule !== '') {
                return $rule;
            }

            $key = self::ROUTE_KEY_RULE_STATIC;
            throw new InvalidArgumentException("Telegram route key $key must contain non-empty string");
        }

        return null;
    }

    private function compileRoute(array $route): UpdateHandlerInterface
    {
        $middlewares = $route[self::ROUTE_KEY_MIDDLEWARES] ?? [];
        $middlewares[] = $this->getActionWrapped($route[self::ROUTE_KEY_ACTION]);
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

    private function getActionWrapped(mixed $definition): Closure
    {
        if (is_string($definition)) {
            return fn(Update $update, UpdateHandlerInterface $handler): ResponseInterface => $this
                ->container
                ->get($definition)
                ->handle($update);
        }

        if ($definition instanceof self) {
            return static fn(Update $update, UpdateHandlerInterface $handler): ResponseInterface => $definition
                ->match($update)
                ->handle($update);
        }

        if ($definition instanceof UpdateHandlerInterface) {
            return static fn(Update $update, UpdateHandlerInterface $handler): ResponseInterface => $definition->handle($update);
        }

        throw new InvalidArgumentException(
            'Telegram route action must be a string identifier of an UpdateHandlerInterface class.'
        );
    }

    private function getEmptyFallbackHandler(): UpdateHandlerInterface
    {
        if ($this->emptyFallbackHandler === null) {
            $this->emptyFallbackHandler = new class implements UpdateHandlerInterface {
                public function handle(Update $update): ResponseInterface
                {
                    return new Response();
                }
            };
        }

        return $this->emptyFallbackHandler;
    }
}
