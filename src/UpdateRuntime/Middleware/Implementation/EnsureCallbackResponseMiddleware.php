<?php

declare(strict_types=1);

namespace Botasis\Runtime\UpdateRuntime\Middleware\Implementation;

use Botasis\Client\Telegram\Entity\CallbackResponse;
use Botasis\Runtime\Update\Update;
use Botasis\Runtime\Response\ResponseInterface;
use Botasis\Runtime\UpdateRuntime\Middleware\MiddlewareInterface;
use Botasis\Runtime\UpdateRuntime\RequestHandlerInterface;

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
