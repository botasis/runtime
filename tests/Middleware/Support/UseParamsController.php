<?php

declare(strict_types=1);

namespace Botasis\Runtime\Tests\Middleware\Support;

use Botasis\Client\Telegram\Entity\Message\Message;
use Botasis\Client\Telegram\Entity\Message\MessageFormat;
use Botasis\Runtime\Response\ResponseInterface;
use Botasis\Runtime\Update\Update;
use Botasis\Runtime\UpdateHandlerInterface;

final class UseParamsController
{
    public function index(Update $request, UpdateHandlerInterface $handler): ResponseInterface
    {
        return $handler
            ->handle($request)
            ->withMessage(
                new Message('message text', MessageFormat::TEXT, $request->chatId)
            );
    }
}
