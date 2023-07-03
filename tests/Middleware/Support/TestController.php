<?php

declare(strict_types=1);

namespace Botasis\Runtime\Tests\Middleware\Support;

use Botasis\Client\Telegram\Request\Message\Message;
use Botasis\Client\Telegram\Request\Message\MessageFormat;
use Botasis\Runtime\Response\Response;
use Botasis\Runtime\Response\ResponseInterface;

final class TestController
{
    public function index(): ResponseInterface
    {
        return (new Response())
            ->withRequest(new Message('test message', MessageFormat::TEXT, 'chatId'));
    }
}
