<?php

declare(strict_types=1);

namespace Viktorprogger\TelegramBot\Tests\UpdateRuntime\Middleware\Support;

use Viktorprogger\TelegramBot\Response\Message\MessageFormat;
use Viktorprogger\TelegramBot\Response\Message\TelegramMessage;
use Viktorprogger\TelegramBot\Response\Response;
use Viktorprogger\TelegramBot\Response\ResponseInterface;

final class TestController
{
    public function index(): ResponseInterface
    {
        return (new Response())
            ->withMessage(new TelegramMessage('test message', MessageFormat::TEXT, 'chatId'));
    }
}
