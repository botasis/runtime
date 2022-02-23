<?php

declare(strict_types=1);

namespace Viktorprogger\TelegramBot\Tests\Domain\UpdateRuntime\Middleware\Support;

use Viktorprogger\TelegramBot\Domain\Client\Response;
use Viktorprogger\TelegramBot\Domain\Client\ResponseInterface;
use Viktorprogger\TelegramBot\Domain\Client\TelegramCallbackResponse;
use Viktorprogger\TelegramBot\Domain\UpdateRuntime\Middleware\MiddlewareInterface;
use Viktorprogger\TelegramBot\Domain\UpdateRuntime\RequestHandlerInterface;
use Viktorprogger\TelegramBot\Domain\UpdateRuntime\TelegramRequest;

final class UseParamsMiddleware implements MiddlewareInterface
{
    public function process(TelegramRequest $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return (new Response())->withCallbackResponse(new TelegramCallbackResponse('fake-id'));
    }
}
