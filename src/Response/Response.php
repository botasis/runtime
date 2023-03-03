<?php

declare(strict_types=1);

namespace Viktorprogger\TelegramBot\Response;

use Botasis\Client\Telegram\Entity\CallbackResponse;
use Botasis\Client\Telegram\Entity\InlineKeyboard\InlineKeyboardUpdate;
use Botasis\Client\Telegram\Entity\Message\Message;
use Botasis\Client\Telegram\Entity\Message\MessageUpdate;

final class Response implements ResponseInterface
{
    /** @var Message[] */
    private array $messages = [];

    private ?CallbackResponse $callbackResponse = null;

    /** @var InlineKeyboardUpdate[] */
    private array $keyboardUpdates = [];

    /** @var MessageUpdate[] */
    private array $messageUpdates = [];

    public function withMessage(Message $message): ResponseInterface
    {
        $instance = clone $this;
        $instance->messages[] = $message;

        return $instance;
    }

    public function withMessageUpdate(MessageUpdate $message): ResponseInterface
    {
        $instance = clone $this;
        $instance->messageUpdates[] = $message;

        return $instance;
    }

    public function withCallbackResponse(CallbackResponse $callbackResponse): ResponseInterface
    {
        $instance = clone $this;
        $instance->callbackResponse = $callbackResponse;

        return $instance;
    }

    public function withKeyboardUpdate(InlineKeyboardUpdate $update): ResponseInterface
    {
        $instance = clone $this;
        $instance->keyboardUpdates[] = $update;

        return $instance;
    }

    public function getMessages(): array
    {
        return $this->messages;
    }

    public function getMessageUpdates(): array
    {
        return $this->messageUpdates;
    }

    public function getCallbackResponse(): ?CallbackResponse
    {
        return $this->callbackResponse;
    }

    public function getKeyboardUpdates(): array
    {
        return $this->keyboardUpdates;
    }
}
