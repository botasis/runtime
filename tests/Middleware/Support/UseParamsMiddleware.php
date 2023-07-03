<?php

declare(strict_types=1);

namespace Botasis\Runtime\Tests\Middleware\Support;

use Botasis\Client\Telegram\Request\CallbackResponse;
use Botasis\Runtime\Middleware\MiddlewareInterface;
use Botasis\Runtime\Response\Response;
use Botasis\Runtime\Response\ResponseInterface;
use Botasis\Runtime\Update\Update;
use Botasis\Runtime\UpdateHandlerInterface;

final class UseParamsMiddleware implements MiddlewareInterface
{
    public function process(Update $request, UpdateHandlerInterface $handler): ResponseInterface
    {
        return (new Response())->withRequest(new CallbackResponse('fake-id'));
    }
}
