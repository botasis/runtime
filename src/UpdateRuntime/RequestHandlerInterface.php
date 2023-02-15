<?php

declare(strict_types=1);

namespace Viktorprogger\TelegramBot\UpdateRuntime;

use Viktorprogger\TelegramBot\Request\TelegramRequest;
use Viktorprogger\TelegramBot\Response\ResponseInterface;

interface RequestHandlerInterface
{
    /**
     * Handles a request and produces a response.
     *
     * May call other collaborating code to generate the response.
     */
    public function handle(TelegramRequest $request): ResponseInterface;
}
