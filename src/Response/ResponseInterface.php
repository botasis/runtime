<?php

declare(strict_types=1);

namespace Viktorprogger\TelegramBot\Response;

use Botasis\Client\Telegram\Entity\CallbackResponse;
use Botasis\Client\Telegram\Entity\InlineKeyboard\InlineKeyboardUpdate;
use Botasis\Client\Telegram\Entity\Message\Message;
use Botasis\Client\Telegram\Entity\Message\MessageUpdate;

interface ResponseInterface
{
    public function withMessage(Message $message): ResponseInterface;

    public function withMessageUpdate(MessageUpdate $message): ResponseInterface;

    public function withCallbackResponse(CallbackResponse $callbackResponse): ResponseInterface;

    public function withKeyboardUpdate(InlineKeyboardUpdate $update): ResponseInterface;

    public function getMessages(): array;

    public function getMessageUpdates(): array;

    public function getCallbackResponse(): ?CallbackResponse;

    public function getKeyboardUpdates(): array;
}
