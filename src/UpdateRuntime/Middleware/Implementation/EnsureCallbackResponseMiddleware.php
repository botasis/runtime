<?php

declare(strict_types=1);

namespace Viktorprogger\TelegramBot\UpdateRuntime\Middleware\Implementation;

use Botasis\Client\Telegram\Entity\CallbackResponse;
use Viktorprogger\TelegramBot\Update\Update;
use Viktorprogger\TelegramBot\Response\ResponseInterface;
use Viktorprogger\TelegramBot\UpdateRuntime\Middleware\MiddlewareInterface;
use Viktorprogger\TelegramBot\UpdateRuntime\RequestHandlerInterface;

final class EnsureCallbackResponseMiddleware implements MiddlewareInterface
{
    public function process(Update $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        if ($request->callbackQueryId !== null && $response->getCallbackResponse() === null) {
            $response = $response->withCallbackResponse(new CallbackResponse($request->callbackQueryId));
        }

        return $response;
    }
}
