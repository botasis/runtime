<?php

declare(strict_types=1);

namespace Viktorprogger\TelegramBot\Domain\Client;

use Viktorprogger\TelegramBot\Domain\Client\InlineKeyboardButton;
use Viktorprogger\TelegramBot\Domain\Client\MessageFormat;

final class TelegramMessageUpdate
{
    /**
     * @param InlineKeyboardButton[][] $inlineKeyboard
     */
    public function __construct(
        public readonly string $text,
        public readonly MessageFormat $format,
        public readonly string $chatId,
        public readonly string $messageId,
        public readonly array $inlineKeyboard = [],
    ) {
    }

    public function getArray(): array
    {
        $result = [
            'text' => $this->text,
            'chat_id' => $this->chatId,
            'message_id' => $this->messageId,
        ];

        if ($this->format->isMarkdown()) {
            $result['parse_mode'] = 'MarkdownV2';
        } elseif ($this->format->isHtml()) {
            $result['parse_mode'] = 'HTML';
        }

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
