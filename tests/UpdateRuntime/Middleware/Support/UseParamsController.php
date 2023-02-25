<?php

declare(strict_types=1);

namespace Viktorprogger\TelegramBot\Tests\UpdateRuntime\Middleware\Support;

use Viktorprogger\TelegramBot\Request\TelegramRequest;
use Viktorprogger\TelegramBot\Response\Message\MessageFormat;
use Viktorprogger\TelegramBot\Response\Message\TelegramMessage;
use Viktorprogger\TelegramBot\Response\ResponseInterface;
use Viktorprogger\TelegramBot\UpdateRuntime\RequestHandlerInterface;

final class UseParamsController
{
    public function index(TelegramRequest $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $handler
            ->handle($request)
            ->withMessage(
                new TelegramMessage('message text', MessageFormat::TEXT, $request->chatId)
            );
    }
}
