<?php

declare(strict_types=1);

namespace Botasis\Runtime\State;

use Stringable;

interface StateInterface
{
    /**
     * @return string|null Telegram user id
     */
    public function getUserId(): ?string;

    /**
     * @return string|null Telegram chat id
     */
    public function getChatId(): ?string;

    /**
     * @return string|Stringable|null Application state data
     */
    public function getData(): string|Stringable|null;
}
