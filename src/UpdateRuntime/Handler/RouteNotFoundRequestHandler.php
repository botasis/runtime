<?php

declare(strict_types=1);

namespace Viktorprogger\TelegramBot\UpdateRuntime\Handler;

use Viktorprogger\TelegramBot\Request\TelegramRequest;
use Viktorprogger\TelegramBot\UpdateRuntime\RequestHandlerInterface;
use Viktorprogger\TelegramBot\Response\Message\MessageFormat;
use Viktorprogger\TelegramBot\Response\Message\TelegramMessage;
use Viktorprogger\TelegramBot\Response\Response;
use Viktorprogger\TelegramBot\Response\ResponseInterface;

final readonly class RouteNotFoundRequestHandler implements RequestHandlerInterface
{
    public function __construct(private string $message = 'Your request is invalid, I don\'t know what to say.')
    {
    }

    public function handle(TelegramRequest $request): ResponseInterface
    {
        return (new Response())
            ->withMessage(new TelegramMessage($this->message, MessageFormat::TEXT, $request->chatId));
    }
}
