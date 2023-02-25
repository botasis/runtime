<?php

declare(strict_types=1);

namespace Viktorprogger\TelegramBot\UpdateRuntime\Middleware;

use Viktorprogger\TelegramBot\Request\TelegramRequest;
use Viktorprogger\TelegramBot\UpdateRuntime\RequestHandlerInterface;
use Viktorprogger\TelegramBot\Response\ResponseInterface;

interface MiddlewareInterface
{
    public function process(TelegramRequest $request, RequestHandlerInterface $handler): ResponseInterface;
}
