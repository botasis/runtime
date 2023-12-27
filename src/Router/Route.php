<?php

declare(strict_types=1);

namespace Botasis\Runtime\Router;

use Botasis\Runtime\Middleware\MiddlewareInterface;
use Botasis\Runtime\UpdateHandlerInterface;

final class Route
{
    private array $middlewares = [];

    /**
     * @param class-string<UpdateHandlerInterface>|UpdateHandlerInterface $action
     */
    public function __construct(
        public readonly RuleStatic|RuleDynamic $rule,
        public readonly string|UpdateHandlerInterface $action,
    ) {
    }

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
