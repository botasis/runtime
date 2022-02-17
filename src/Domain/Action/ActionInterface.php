<?php

declare(strict_types=1);

namespace Viktorprogger\TelegramBot\Domain\Action;

use Viktorprogger\TelegramBot\Domain\Client\Response;
use Viktorprogger\TelegramBot\Domain\UpdateRuntime\TelegramRequest;

interface ActionInterface
{
    public function handle(TelegramRequest $request, Response $response): Response;
}
