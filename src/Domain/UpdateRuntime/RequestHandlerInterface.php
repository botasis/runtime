<?php

declare(strict_types=1);

namespace Viktorprogger\TelegramBot\Domain\UpdateRuntime;

use Viktorprogger\TelegramBot\Domain\Client\ResponseInterface;

interface RequestHandlerInterface
{
    /**
     * Handles a request and produces a response.
     *
     * May call other collaborating code to generate the response.
     */
    public function handle(TelegramRequest $request): ResponseInterface;
}
