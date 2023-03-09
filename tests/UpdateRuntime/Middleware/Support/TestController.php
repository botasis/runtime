<?php

declare(strict_types=1);

namespace Botasis\Runtime\Tests\UpdateRuntime\Middleware\Support;

use Botasis\Client\Telegram\Entity\Message\Message;
use Botasis\Client\Telegram\Entity\Message\MessageFormat;
use Botasis\Runtime\Response\Response;
use Botasis\Runtime\Response\ResponseInterface;

final class TestController
{
    public function index(): ResponseInterface
    {
        return (new Response())
            ->withMessage(new Message('test message', MessageFormat::TEXT, 'chatId'));
    }
}
