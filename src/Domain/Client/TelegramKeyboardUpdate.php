<?php

declare(strict_types=1);

namespace Viktorprogger\TelegramBot\Domain\Client;

final class TelegramKeyboardUpdate
{
    /**
     * @param InlineKeyboardButton[][] $inlineKeyboard
     */
    public function __construct(
        private readonly string $chatId,
        public readonly string $messageId,
        private readonly array $inlineKeyboard = [],
    ) {
    }

    public function getArray(): array
    {
        $result = [
            'chat_id' => $this->chatId,
            'message_id' => $this->messageId,
            'reply_markup' => [],
        ];

        foreach ($this->inlineKeyboard as $i => $row) {
            foreach ($row as $button) {
                $result['reply_markup']['inline_keyboard'][$i][] = [
                    'text' => $button->getLabel(),
                    'callback_data' => $button->getCallbackData(),
                ];
            }
        }

        return $result;
    }
}
