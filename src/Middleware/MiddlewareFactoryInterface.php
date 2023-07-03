<?php

declare(strict_types=1);

namespace Botasis\Runtime\Middleware;

/**
 * Creates a middleware based on the definition provided.
 * You may implement this interface if you want to introduce custom definitions or pass additional data to
 * the middleware created.
 */
interface MiddlewareFactoryInterface
{
    /**
     * Create a middleware based on definition provided.
     *
     * @param MiddlewareInterface|callable|array|string $middlewareDefinition Middleware definition to use.
     */
    public function create(MiddlewareInterface|callable|array|string $middlewareDefinition): MiddlewareInterface;
}
