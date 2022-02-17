<?php

declare(strict_types=1);

namespace Viktorprogger\TelegramBot\Domain\Client;

final class Response
{
    /** @var TelegramMessage[] */
    private array $messages = [];

    private ?TelegramCallbackResponse $callbackResponse = null;

    /** @var TelegramKeyboardUpdate[] */
    private array $keyboardUpdates = [];

    /** @var TelegramMessageUpdate[] */
    private array $messageUpdates = [];

    public function withMessage(TelegramMessage $message): self
    {
        $instance = clone $this;
        $instance->messages[] = $message;

        return $instance;
    }

    public function withMessageUpdate(TelegramMessageUpdate $message): self
    {
        $instance = clone $this;
        $instance->messageUpdates[] = $message;

        return $instance;
    }

    public function withCallbackResponse(TelegramCallbackResponse $callbackResponse): self
    {
        $instance = clone $this;
        $instance->callbackResponse = $callbackResponse;

        return $instance;
    }

    public function withKeyboardUpdate(TelegramKeyboardUpdate $update): self
    {
        $instance = clone $this;
        $instance->keyboardUpdates[] = $update;

        return $instance;
    }

    public function getMessages(): array
    {
        return $this->messages;
    }

    public function getMessageUpdates()
    {
        return $this->messageUpdates;
    }

    public function getCallbackResponse(): ?TelegramCallbackResponse
    {
        return $this->callbackResponse;
    }

    public function getKeyboardUpdates(): array
    {
        return $this->keyboardUpdates;
    }
}
