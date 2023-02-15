<?php

declare(strict_types=1);

namespace Viktorprogger\TelegramBot\UpdateRuntime\Middleware\Implementation;

use Viktorprogger\TelegramBot\Request\TelegramRequest;
use Viktorprogger\TelegramBot\Response\ResponseInterface;
use Viktorprogger\TelegramBot\UpdateRuntime\Middleware\MiddlewareInterface;
use Viktorprogger\TelegramBot\UpdateRuntime\NotFoundException;
use Viktorprogger\TelegramBot\UpdateRuntime\RequestHandlerInterface;
use Viktorprogger\TelegramBot\UpdateRuntime\Router;

final readonly class RouterMiddleware implements MiddlewareInterface
{
    public function __construct(private Router $router)
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
