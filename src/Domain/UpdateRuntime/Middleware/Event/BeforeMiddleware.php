<?php

declare(strict_types=1);

namespace Viktorprogger\TelegramBot\Domain\UpdateRuntime\Middleware\Event;

use Viktorprogger\TelegramBot\Domain\Entity\Request\TelegramRequest;
use Viktorprogger\TelegramBot\Domain\UpdateRuntime\Middleware\MiddlewareInterface;

/**
 * BeforeMiddleware event is raised before executing a middleware.
 */
final class BeforeMiddleware
{
    private MiddlewareInterface $middleware;
    private TelegramRequest $request;

    /**
     * @param MiddlewareInterface $middleware Middleware to be executed.
     * @param TelegramRequest $request Request to be passed to the middleware.
     */
    public function __construct(MiddlewareInterface $middleware, TelegramRequest $request)
    {
        $this->middleware = $middleware;
        $this->request = $request;
    }

    /**
     * @return MiddlewareInterface Middleware to be executed.
     */
    public function getMiddleware(): MiddlewareInterface
    {
        return $this->middleware;
    }

    /**
     * @return TelegramRequest Request to be passed to the middleware.
     */
    public function getRequest(): TelegramRequest
    {
        return $this->request;
    }
}
