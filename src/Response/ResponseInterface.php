<?php

declare(strict_types=1);

namespace Botasis\Runtime\Response;

use Botasis\Client\Telegram\Request\TelegramRequestInterface;
use Botasis\Runtime\Request\TelegramRequestDecorator;
use Botasis\Runtime\Update\Update;

interface ResponseInterface
{
    public function withUpdate(Update $update): ResponseInterface;

    public function withRequest(TelegramRequestDecorator $request): ResponseInterface;

    public function withRequestReplaced(TelegramRequestInterface $search, ?TelegramRequestDecorator $replace): ResponseInterface;

    public function getUpdate(): Update;

    /**
     * @return TelegramRequestDecorator[]
     */
    public function getRequests(): array;

    public function hasCallbackResponse(): bool;
}
