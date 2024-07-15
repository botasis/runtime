<?php

declare(strict_types=1);

namespace Botasis\Runtime\Router;

use Botasis\Runtime\Middleware\MiddlewareInterface;
use Botasis\Runtime\UpdateHandlerInterface;

final class Route
{
    private array $middlewares = [];

    /**
     * @param callable|array|string|object|UpdateHandlerInterface $action
     */
    public function __construct(
        public readonly RuleStatic|RuleDynamic $rule,
        public readonly mixed $action,
    ) {
        /**
         * These checks should be done for projects without Psalm
         * @psalm-suppress DocblockTypeContradiction
         * @psalm-suppress RedundantCondition
         */
        if (
            !is_callable($this->action)
            && !is_object($this->action)
            && !is_string($this->action)
            && !is_array($this->action)
        ) {
            throw new \InvalidArgumentException('Action must be callable, object, string or array.');
        }
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
