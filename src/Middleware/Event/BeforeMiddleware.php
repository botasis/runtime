<?php

declare(strict_types=1);

namespace Botasis\Runtime\Middleware\Event;

use Botasis\Runtime\Middleware\MiddlewareInterface;
use Botasis\Runtime\Update\Update;

/**
 * BeforeMiddleware event is raised before executing a middleware.
 */
final readonly class BeforeMiddleware
{
    /**
     * @param MiddlewareInterface $middleware Middleware to be executed.
     * @param Update $update Update to be passed to the middleware.
     */
    public function __construct(
        public MiddlewareInterface $middleware,
        public Update $update
    ) {
    }
}
