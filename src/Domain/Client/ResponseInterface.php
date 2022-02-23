<?php

declare(strict_types=1);

namespace Viktorprogger\TelegramBot\Domain\Client;

interface ResponseInterface
{
    public function withMessage(TelegramMessage $message): ResponseInterface;

    public function withMessageUpdate(TelegramMessageUpdate $message): ResponseInterface;

    public function withCallbackResponse(TelegramCallbackResponse $callbackResponse): ResponseInterface;

    public function withKeyboardUpdate(TelegramKeyboardUpdate $update): ResponseInterface;

    public function getMessages(): array;

    public function getMessageUpdates(): array;

    public function getCallbackResponse(): ?TelegramCallbackResponse;

    public function getKeyboardUpdates(): array;
}
