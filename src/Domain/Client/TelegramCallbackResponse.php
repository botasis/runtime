<?php

declare(strict_types=1);

namespace Viktorprogger\TelegramBot\Domain\Client;

final readonly class TelegramCallbackResponse
{
    public function __construct(
        public string $id,
        public ?string $text = null,
        public bool $showAlert = false,
        public ?string $url = null,
        public int $cacheTime = 0,
    ) {
    }

    /**
     * @deprecated Will be removed before the first release
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @deprecated Will be removed before the first release
     */
    public function getText(): ?string
    {
        return $this->text;
    }

    /**
     * @deprecated Will be removed before the first release
     */
    public function isShowAlert(): bool
    {
        return $this->showAlert;
    }

    /**
     * @deprecated Will be removed before the first release
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * @deprecated Will be removed before the first release
     */
    public function getCacheTime(): int
    {
        return $this->cacheTime;
    }
}
