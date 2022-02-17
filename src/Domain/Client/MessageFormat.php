<?php

declare(strict_types=1);

namespace Viktorprogger\TelegramBot\Domain\Client;

use InvalidArgumentException;

final class MessageFormat
{
    public const TEXT = 1;
    public const MARKDOWN = 2;
    public const HTML = 3;

    private static array $instances = [];

    private function __construct(private int $format)
    {
    }

    public static function getInstance(int $format)
    {
        if (!in_array($format, [self::TEXT, self::MARKDOWN, self::HTML], true)) {
            throw new InvalidArgumentException('Invalid format value');
        }

        if (!isset(self::$instances[$format])) {
            self::$instances[$format] = new self($format);
        }

        return self::$instances[$format];
    }

    public static function text(): self
    {
        return self::getInstance(self::TEXT);
    }

    public static function markdown(): self
    {
        return self::getInstance(self::MARKDOWN);
    }

    public static function html(): self
    {
        return self::getInstance(self::HTML);
    }

    public function isText(): bool
    {
        return $this->format === self::TEXT;
    }

    public function isMarkdown(): bool
    {
        return $this->format === self::MARKDOWN;
    }

    public function isHtml(): bool
    {
        return $this->format === self::HTML;
    }
}
