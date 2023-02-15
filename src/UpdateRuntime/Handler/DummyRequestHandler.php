<?php

declare(strict_types=1);

namespace Viktorprogger\TelegramBot\UpdateRuntime\Handler;

use Viktorprogger\TelegramBot\Request\TelegramRequest;
use Viktorprogger\TelegramBot\Response\Response;
use Viktorprogger\TelegramBot\Response\ResponseInterface;
use Viktorprogger\TelegramBot\UpdateRuntime\RequestHandlerInterface;

final class DummyRequestHandler implements RequestHandlerInterface
{
    public function handle(TelegramRequest $request): ResponseInterface
    {
        return new Response();
    }
}
