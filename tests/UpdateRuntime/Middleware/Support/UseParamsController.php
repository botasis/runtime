<?php

declare(strict_types=1);

namespace Botasis\Runtime\Tests\UpdateRuntime\Middleware\Support;

use Botasis\Client\Telegram\Entity\Message\Message;
use Botasis\Client\Telegram\Entity\Message\MessageFormat;
use Botasis\Runtime\Update\Update;
use Botasis\Runtime\Response\ResponseInterface;
use Botasis\Runtime\UpdateRuntime\RequestHandlerInterface;

final class UseParamsController
{
    public function index(Update $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $handler
            ->handle($request)
            ->withMessage(
                new Message('message text', MessageFormat::TEXT, $request->chatId)
            );
    }
}
