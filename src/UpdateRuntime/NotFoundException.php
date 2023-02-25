<?php

namespace Viktorprogger\TelegramBot\UpdateRuntime;

use RuntimeException;
use Throwable;
use Viktorprogger\TelegramBot\Request\TelegramRequest;

final class NotFoundException extends RuntimeException
{
    protected $message = 'No matches for the request';

    public function __construct(public readonly TelegramRequest $request, int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($this->message, $code, $previous);
    }
}
