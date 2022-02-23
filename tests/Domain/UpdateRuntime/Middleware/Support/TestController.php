<?php

declare(strict_types=1);

namespace Viktorprogger\TelegramBot\Tests\Domain\UpdateRuntime\Middleware\Support;

use Viktorprogger\TelegramBot\Domain\Client\MessageFormat;
use Viktorprogger\TelegramBot\Domain\Client\Response;
use Viktorprogger\TelegramBot\Domain\Client\ResponseInterface;
use Viktorprogger\TelegramBot\Domain\Client\TelegramMessage;

final class TestController
{
    public function index(): ResponseInterface
    {
        return (new Response())
            ->withMessage(new TelegramMessage('test message', MessageFormat::TEXT, 'chatId'));
    }
}
