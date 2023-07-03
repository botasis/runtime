<?php

declare(strict_types=1);

namespace Botasis\Runtime\Handler;

use Botasis\Client\Telegram\Request\Message\Message;
use Botasis\Client\Telegram\Request\Message\MessageFormat;
use Botasis\Runtime\Response\Response;
use Botasis\Runtime\Response\ResponseInterface;
use Botasis\Runtime\Update\Update;
use Botasis\Runtime\UpdateHandlerInterface;

final readonly class RouteNotFoundUpdateHandler implements UpdateHandlerInterface
{
    public function __construct(private string $message = 'Your request is invalid, I don\'t know what to say.')
    {
    }

    public function handle(Update $update): ResponseInterface
    {
        return (new Response())
            ->withRequest(new Message($this->message, MessageFormat::TEXT, $update->chatId));
    }
}
