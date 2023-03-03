<?php

declare(strict_types=1);

namespace Viktorprogger\TelegramBot\Tests\UpdateRuntime\Middleware\Support;

use Botasis\Client\Telegram\Entity\CallbackResponse;
use Viktorprogger\TelegramBot\Update\Update;
use Viktorprogger\TelegramBot\Response\Response;
use Viktorprogger\TelegramBot\Response\ResponseInterface;
use Viktorprogger\TelegramBot\UpdateRuntime\Middleware\MiddlewareInterface;
use Viktorprogger\TelegramBot\UpdateRuntime\RequestHandlerInterface;

final class UseParamsMiddleware implements MiddlewareInterface
{
    public function process(Update $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return (new Response())->withCallbackResponse(new CallbackResponse('fake-id'));
    }
}
