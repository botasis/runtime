<?php

declare(strict_types=1);

namespace Viktorprogger\TelegramBot\Domain\Client;

final class InlineKeyboardButton
{
    public function __construct(
        private string $label,
        private string $callbackData = '',
    ) {
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getCallbackData(): string
    {
        return $this->callbackData;
    }
}
