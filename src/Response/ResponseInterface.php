<?php

declare(strict_types=1);

namespace Botasis\Runtime\Response;

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

    /**
     * @return Message[]
     */
    public function getMessages(): array;

    /**
     * @return MessageUpdate[]
     */
    public function getMessageUpdates(): array;

    public function getCallbackResponse(): ?CallbackResponse;

    /**
     * @return InlineKeyboardUpdate[]
     */
    public function getKeyboardUpdates(): array;
}
