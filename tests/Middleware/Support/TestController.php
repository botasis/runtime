<?php

declare(strict_types=1);

namespace Botasis\Runtime\Tests\Middleware\Support;

use Botasis\Client\Telegram\Request\Message\Message;
use Botasis\Client\Telegram\Request\Message\MessageFormat;
use Botasis\Runtime\Request\TelegramRequestDecorator;
use Botasis\Runtime\Response\Response;
use Botasis\Runtime\Response\ResponseInterface;
use Botasis\Runtime\Update\Update;

final class TestController
{
    public function index(Update $update): ResponseInterface
    {
        return (new Response($update))
            ->withRequest(
                new TelegramRequestDecorator(
                    new Message('test message', MessageFormat::TEXT, 'chatId')
                )
            );
    }
}
