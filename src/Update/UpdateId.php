<?php

declare(strict_types=1);

namespace Viktorprogger\TelegramBot\Update;

final readonly class UpdateId
{
    public function __construct(public int $value)
    {
    }
}
