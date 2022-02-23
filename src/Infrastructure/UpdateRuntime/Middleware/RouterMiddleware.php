<?php

declare(strict_types=1);

namespace Viktorprogger\TelegramBot\Infrastructure\UpdateRuntime\Middleware;

use Viktorprogger\TelegramBot\Domain\Client\ResponseInterface;
use Viktorprogger\TelegramBot\Domain\UpdateRuntime\Middleware\MiddlewareInterface;
use Viktorprogger\TelegramBot\Domain\UpdateRuntime\NotFoundException;
use Viktorprogger\TelegramBot\Domain\UpdateRuntime\RequestHandlerInterface;
use Viktorprogger\TelegramBot\Domain\UpdateRuntime\Router;
use Viktorprogger\TelegramBot\Domain\UpdateRuntime\TelegramRequest;

final class RouterMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly Router $router)
    {
    }

    public function process(TelegramRequest $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $this->router->match($request)->handle($request);
        } catch (NotFoundException) {
            return $handler->handle($request);
        }
    }
}
