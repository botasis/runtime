<?php

declare(strict_types=1);

namespace Viktorprogger\TelegramBot\Domain\UpdateRuntime\Middleware\Event;

use Viktorprogger\TelegramBot\Domain\Entity\Request\TelegramRequest;
use Viktorprogger\TelegramBot\Domain\UpdateRuntime\Middleware\MiddlewareInterface;

/**
 * BeforeMiddleware event is raised before executing a middleware.
 */
final readonly class BeforeMiddleware
{
    /**
     * @param MiddlewareInterface $middleware Middleware to be executed.
     * @param TelegramRequest $request Request to be passed to the middleware.
     */
    public function __construct(
        public MiddlewareInterface $middleware,
        public TelegramRequest $request
    ) {
    }
}
