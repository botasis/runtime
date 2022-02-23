<?php

declare(strict_types=1);

namespace Viktorprogger\TelegramBot\Domain\UpdateRuntime\Middleware;

/**
 * Creates a PSR-15 middleware based on the definition provided.
 * You may implement this interface if you want to introduce custom definitions or pass additional data to
 * the middleware created.
 */
interface MiddlewareFactoryInterface
{
    /**
     * Create a middleware based on definition provided.
     *
     * @param callable|array|string $middlewareDefinition Middleware definition to use.
     */
    public function create(callable|array|string $middlewareDefinition): MiddlewareInterface;
}
