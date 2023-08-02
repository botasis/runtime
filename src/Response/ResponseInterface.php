<?php

declare(strict_types=1);

namespace Botasis\Runtime\Response;

use Botasis\Client\Telegram\Request\TelegramRequestInterface;
use Botasis\Runtime\Update\Update;

interface ResponseInterface
{
    public function withUpdate(Update $update): ResponseInterface;

    public function withRequest(TelegramRequestInterface $request): ResponseInterface;

    public function withRequestReplaced(TelegramRequestInterface $search, ?TelegramRequestInterface $replace): ResponseInterface;

    public function getUpdate(): Update;

    /**
     * @return TelegramRequestInterface[]
     */
    public function getRequests(): array;

    public function hasCallbackResponse(): bool;
}
