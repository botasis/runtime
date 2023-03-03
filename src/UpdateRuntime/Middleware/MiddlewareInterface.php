<?php

declare(strict_types=1);

namespace Viktorprogger\TelegramBot\UpdateRuntime\Middleware;

use Viktorprogger\TelegramBot\Update\Update;
use Viktorprogger\TelegramBot\UpdateRuntime\RequestHandlerInterface;
use Viktorprogger\TelegramBot\Response\ResponseInterface;

interface MiddlewareInterface
{
    public function process(Update $request, RequestHandlerInterface $handler): ResponseInterface;
}
