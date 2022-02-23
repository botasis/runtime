<?php

declare(strict_types=1);

namespace Viktorprogger\TelegramBot\Tests\Domain\UpdateRuntime\Middleware\Support;

use Viktorprogger\TelegramBot\Domain\Client\MessageFormat;
use Viktorprogger\TelegramBot\Domain\Client\Response;
use Viktorprogger\TelegramBot\Domain\Client\ResponseInterface;
use Viktorprogger\TelegramBot\Domain\Client\TelegramMessage;
use Viktorprogger\TelegramBot\Domain\UpdateRuntime\RequestHandlerInterface;
use Viktorprogger\TelegramBot\Domain\UpdateRuntime\TelegramRequest;

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
