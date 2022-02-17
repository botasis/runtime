<?php

declare(strict_types=1);

namespace Viktorprogger\TelegramBot\Domain\Client;

final class TelegramCallbackResponse
{
    public function __construct(
        private string $id,
        private ?string $text = null,
        private bool $showAlert = false,
        private ?string $url = null,
        private int $cacheTime = 0,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function isShowAlert(): bool
    {
        return $this->showAlert;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function getCacheTime(): int
    {
        return $this->cacheTime;
    }
}
