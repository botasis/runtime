<?php

declare(strict_types=1);

namespace Botasis\Runtime\Router;

use Botasis\Runtime\Middleware\MiddlewareInterface;

/**
 * @template-covariant T of RuleStatic|RuleDynamic
 */
final class Group
{
    /**
     * @var Group[]|Route[]
     */
    public readonly array $routes;
    private array $middlewares = [];

    /**
     * @psalm-param T $rule
     */
    public function __construct(
        public readonly RuleStatic|RuleDynamic $rule,
        Route|Group ...$routes,
    ) {
        $this->routes = $routes;
    }

    /**
     * @param class-string<MiddlewareInterface>|array{0:class-string, 1:string}|callable|MiddlewareInterface ...$middlewares
     * @return $this
     */
    public function withMiddlewares(callable|array|string|MiddlewareInterface ...$middlewares): self
    {
        $new = clone $this;
        $new->middlewares = $middlewares;

        return $new;
    }

    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }
}
