<?php

declare(strict_types=1);

namespace Viktorprogger\TelegramBot\Domain\Entity\Request;

final class RequestId
{
    public function __construct(public readonly int $value)
    {
    }
}
