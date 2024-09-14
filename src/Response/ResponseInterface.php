<?php

declare(strict_types=1);

namespace Botasis\Runtime\Response;

use Botasis\Client\Telegram\Request\TelegramRequestInterface;
use Botasis\Runtime\Request\TelegramRequestEnriched;
use Botasis\Runtime\Update\Update;

interface ResponseInterface
{
    public function withUpdate(Update $update): ResponseInterface;

    public function withRequest(TelegramRequestInterface $request, string ...$tags): ResponseInterface;

    /**
     * @param TelegramRequestInterface $search Request to be replaced
     * @param TelegramRequestInterface|null $replace Request to replace with
     * @param string ...$replaceTags Old tags will be replaced with this tag set if $replace !== null
     *
     * @return ResponseInterface
     */
    public function withRequestReplaced(
        TelegramRequestInterface $search,
        ?TelegramRequestInterface $replace,
        string ...$replaceTags
    ): ResponseInterface;

    public function getUpdate(): Update;

    /**
     * @return TelegramRequestEnriched[]
     */
    public function getRequests(): array;

    public function hasCallbackResponse(): bool;
}
