<?php

declare(strict_types=1);

namespace Botasis\Runtime\Tests\UpdateRuntime\Middleware\Support;

use Botasis\Client\Telegram\Entity\CallbackResponse;
use Botasis\Runtime\Update\Update;
use Botasis\Runtime\Response\Response;
use Botasis\Runtime\Response\ResponseInterface;
use Botasis\Runtime\UpdateRuntime\Middleware\MiddlewareInterface;
use Botasis\Runtime\UpdateRuntime\RequestHandlerInterface;

final class UseParamsMiddleware implements MiddlewareInterface
{
    public function process(Update $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return (new Response())->withCallbackResponse(new CallbackResponse('fake-id'));
    }
}
