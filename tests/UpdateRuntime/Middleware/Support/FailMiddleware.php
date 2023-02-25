<?php

declare(strict_types=1);

namespace Viktorprogger\TelegramBot\Tests\UpdateRuntime\Middleware\Support;

use RuntimeException;
use Viktorprogger\TelegramBot\Request\TelegramRequest;
use Viktorprogger\TelegramBot\Response\ResponseInterface;
use Viktorprogger\TelegramBot\UpdateRuntime\Middleware\MiddlewareInterface;
use Viktorprogger\TelegramBot\UpdateRuntime\RequestHandlerInterface;

final class FailMiddleware implements MiddlewareInterface
{
    public function process(TelegramRequest $request, RequestHandlerInterface $handler): ResponseInterface
    {
        throw new RuntimeException('Middleware failed.');
    }
}
