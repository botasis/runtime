<?php

declare(strict_types=1);

namespace Botasis\Runtime\UpdateRuntime\Handler;

use Botasis\Client\Telegram\Entity\Message\Message;
use Botasis\Client\Telegram\Entity\Message\MessageFormat;
use Botasis\Runtime\Update\Update;
use Botasis\Runtime\UpdateRuntime\RequestHandlerInterface;
use Botasis\Runtime\Response\Response;
use Botasis\Runtime\Response\ResponseInterface;

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
