<?php

declare(strict_types=1);

namespace Viktorprogger\TelegramBot\UpdateRuntime\Middleware\Implementation;

use Viktorprogger\TelegramBot\Request\TelegramRequest;
use Viktorprogger\TelegramBot\UpdateRuntime\Middleware\MiddlewareInterface;
use Viktorprogger\TelegramBot\UpdateRuntime\RequestHandlerInterface;
use Viktorprogger\TelegramBot\Response\ResponseInterface;
use Viktorprogger\TelegramBot\Response\TelegramCallbackResponse;

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
