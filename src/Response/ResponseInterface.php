<?php

declare(strict_types=1);

namespace Botasis\Runtime\Response;

use Botasis\Client\Telegram\Request\TelegramRequestInterface;

interface ResponseInterface
{
    public function withRequest(TelegramRequestInterface $request): ResponseInterface;

    public function withRequestReplaced(TelegramRequestInterface $search, ?TelegramRequestInterface $replace): ResponseInterface;

    /**
     * @return TelegramRequestInterface[]
     */
    public function getRequests(): array;

    public function hasCallbackResponse(): bool;
}
