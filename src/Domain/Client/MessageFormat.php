<?php

declare(strict_types=1);

namespace Viktorprogger\TelegramBot\Domain\Client;

enum MessageFormat: int
{
    case TEXT = 1;
    case MARKDOWN = 2;
    case HTML = 3;
}
