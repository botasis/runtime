<?php

declare(strict_types=1);

namespace Viktorprogger\TelegramBot\UpdateRuntime\Handler;

use Botasis\Client\Telegram\Entity\Message\Message;
use Botasis\Client\Telegram\Entity\Message\MessageFormat;
use Viktorprogger\TelegramBot\Update\Update;
use Viktorprogger\TelegramBot\UpdateRuntime\RequestHandlerInterface;
use Viktorprogger\TelegramBot\Response\Response;
use Viktorprogger\TelegramBot\Response\ResponseInterface;

final readonly class RouteNotFoundRequestHandler implements RequestHandlerInterface
{
    public function __construct(private string $message = 'Your request is invalid, I don\'t know what to say.')
    {
    }

    public function handle(Update $update): ResponseInterface
    {
        return (new Response())
            ->withMessage(new Message($this->message, MessageFormat::TEXT, $update->chatId));
    }
}
