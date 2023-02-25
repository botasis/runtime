<?php

declare(strict_types=1);

namespace Viktorprogger\TelegramBot\Tests\UpdateRuntime\Middleware\Support;

use Viktorprogger\TelegramBot\Request\TelegramRequest;
use Viktorprogger\TelegramBot\Response\Response;
use Viktorprogger\TelegramBot\Response\ResponseInterface;
use Viktorprogger\TelegramBot\Response\TelegramCallbackResponse;
use Viktorprogger\TelegramBot\UpdateRuntime\Middleware\MiddlewareInterface;
use Viktorprogger\TelegramBot\UpdateRuntime\RequestHandlerInterface;

final class TestMiddleware implements MiddlewareInterface
{
    public function process(TelegramRequest $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return (new Response())->withCallbackResponse(new TelegramCallbackResponse('42'));
    }
}
