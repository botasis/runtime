<?php

declare(strict_types=1);

namespace Viktorprogger\TelegramBot\Infrastructure\UpdateRuntime\Middleware;

use Viktorprogger\TelegramBot\Domain\Client\ResponseInterface;
use Viktorprogger\TelegramBot\Domain\Client\TelegramCallbackResponse;
use Viktorprogger\TelegramBot\Domain\UpdateRuntime\Middleware\MiddlewareInterface;
use Viktorprogger\TelegramBot\Domain\UpdateRuntime\RequestHandlerInterface;
use Viktorprogger\TelegramBot\Domain\UpdateRuntime\TelegramRequest;

final class EnsureCallbackResponseMiddleware implements MiddlewareInterface
{
    public function process(TelegramRequest $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        if ($request->callbackQueryId !== null && $response->getCallbackResponse() === null) {
            $response = $response->withCallbackResponse(new TelegramCallbackResponse($request->callbackQueryId));
        }

        return $response;
    }
}
