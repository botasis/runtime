<?php

declare(strict_types=1);

namespace Viktorprogger\TelegramBot\UpdateRuntime\Middleware\Event;

use Viktorprogger\TelegramBot\Update\Update;
use Viktorprogger\TelegramBot\UpdateRuntime\Middleware\MiddlewareInterface;

/**
 * BeforeMiddleware event is raised before executing a middleware.
 */
final readonly class BeforeMiddleware
{
    /**
     * @param MiddlewareInterface $middleware Middleware to be executed.
     * @param Update $request Request to be passed to the middleware.
     */
    public function __construct(
        public MiddlewareInterface $middleware,
        public Update $request
    ) {
    }
}
