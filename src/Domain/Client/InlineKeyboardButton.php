<?php

declare(strict_types=1);

namespace Viktorprogger\TelegramBot\Domain\Client;

final readonly class InlineKeyboardButton
{
    public function __construct(
        public string $label,
        public string $callbackData = '',
    ) {
    }

    /**
     * @deprecated Will be removed before the first release
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @deprecated Will be removed before the first release
     */
    public function getCallbackData(): string
    {
        return $this->callbackData;
    }
}
