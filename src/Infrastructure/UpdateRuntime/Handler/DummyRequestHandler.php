<?php

declare(strict_types=1);

namespace Viktorprogger\TelegramBot\Infrastructure\UpdateRuntime\Handler;

use Viktorprogger\TelegramBot\Domain\Client\Response;
use Viktorprogger\TelegramBot\Domain\Client\ResponseInterface;
use Viktorprogger\TelegramBot\Domain\Entity\Request\TelegramRequest;
use Viktorprogger\TelegramBot\Domain\UpdateRuntime\RequestHandlerInterface;

final class DummyRequestHandler implements RequestHandlerInterface
{
    public function handle(TelegramRequest $request): ResponseInterface
    {
        return new Response();
    }
}
