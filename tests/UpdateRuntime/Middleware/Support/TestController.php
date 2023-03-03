<?php

declare(strict_types=1);

namespace Viktorprogger\TelegramBot\Tests\UpdateRuntime\Middleware\Support;

use Botasis\Client\Telegram\Entity\Message\Message;
use Botasis\Client\Telegram\Entity\Message\MessageFormat;
use Viktorprogger\TelegramBot\Response\Response;
use Viktorprogger\TelegramBot\Response\ResponseInterface;

final class TestController
{
    public function index(): ResponseInterface
    {
        return (new Response())
            ->withMessage(new Message('test message', MessageFormat::TEXT, 'chatId'));
    }
}
