<?php

declare(strict_types=1);

namespace Viktorprogger\TelegramBot\Response\Keyboard;

final readonly class TelegramKeyboardUpdate
{
    /**
     * @param InlineKeyboardButton[][] $inlineKeyboard
     */
    public function __construct(
        private string $chatId,
        public string $messageId,
        private array $inlineKeyboard = [],
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
                    'text' => $button->label,
                    'callback_data' => $button->callbackData,
                ];
            }
        }

        return $result;
    }
}
