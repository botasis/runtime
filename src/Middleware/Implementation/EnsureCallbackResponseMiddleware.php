<?php

declare(strict_types=1);

namespace Botasis\Runtime\Middleware\Implementation;

use Botasis\Client\Telegram\Request\CallbackResponse;
use Botasis\Runtime\Middleware\MiddlewareInterface;
use Botasis\Runtime\Request\TelegramRequestDecorator;
use Botasis\Runtime\Response\ResponseInterface;
use Botasis\Runtime\Update\Update;
use Botasis\Runtime\UpdateHandlerInterface;

final class EnsureCallbackResponseMiddleware implements MiddlewareInterface
{
    public function process(Update $update, UpdateHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($update);
        if ($update->callbackQueryId !== null && $response->hasCallbackResponse() === false) {
            $response = $response->withRequest(new TelegramRequestDecorator(new CallbackResponse($update->callbackQueryId)));
        }

        return $response;
    }
}
