<?php

declare(strict_types=1);

namespace Viktorprogger\TelegramBot\Domain\Client;

use JetBrains\PhpStorm\ArrayShape;

final class TelegramMessage
{
    /**
     * @param InlineKeyboardButton[][] $inlineKeyboard
     */
    public function __construct(
        public readonly string $text,
        public readonly MessageFormat $format,
        public readonly string $chatId,
        public readonly array $inlineKeyboard = [],
        public readonly bool $disableLinkPreview = false,
    ) {
    }

    /** @psalm-suppress UndefinedAttributeClass */
    #[ArrayShape([
        'text' => "string",
        'chat_id' => "string",
        'disable_web_page_preview' => "bool",
        'parse_mode' => "string",
        'reply_markup' => "null|array"
    ])]
    public function getArray(): array
    {
        $result = [
            'text' => $this->text,
            'chat_id' => $this->chatId,
            'disable_web_page_preview' => $this->disableLinkPreview,
        ];

        if ($this->format === MessageFormat::MARKDOWN) {
            $result['parse_mode'] = 'MarkdownV2';
        } elseif ($this->format === MessageFormat::HTML) {
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

    public function withFormat(MessageFormat $format): self
    {
        return new self(
            $this->text,
            $format,
            $this->chatId,
            $this->inlineKeyboard,
            $this->disableLinkPreview,
        );
    }

    public function withText(string $text): self
    {
        return new self(
            $text,
            $this->format,
            $this->chatId,
            $this->inlineKeyboard,
            $this->disableLinkPreview,
        );
    }
}
