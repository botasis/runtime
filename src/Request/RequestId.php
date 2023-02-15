<?php

declare(strict_types=1);

namespace Viktorprogger\TelegramBot\Request;

final readonly class RequestId
{
    public function __construct(public int $value)
    {
    }
}
