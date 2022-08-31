<?php

declare(strict_types=1);

namespace Viktorprogger\TelegramBot\Infrastructure\UpdateRuntime\Handler;

use Viktorprogger\TelegramBot\Domain\Client\MessageFormat;
use Viktorprogger\TelegramBot\Domain\Client\Response;
use Viktorprogger\TelegramBot\Domain\Client\ResponseInterface;
use Viktorprogger\TelegramBot\Domain\Client\TelegramMessage;
use Viktorprogger\TelegramBot\Domain\Entity\Request\TelegramRequest;
use Viktorprogger\TelegramBot\Domain\UpdateRuntime\RequestHandlerInterface;

final class RouteNotFoundRequestHandler implements RequestHandlerInterface
{
    public function __construct(private readonly string $message = 'Your request is invalid, I don\'t know what to say.')
    {
    }

    public function handle(TelegramRequest $request): ResponseInterface
    {
        return (new Response())
            ->withMessage(new TelegramMessage($this->message, MessageFormat::TEXT, $request->chatId));
    }
}
