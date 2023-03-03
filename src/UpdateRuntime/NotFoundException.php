<?php

namespace Viktorprogger\TelegramBot\UpdateRuntime;

use RuntimeException;
use Throwable;
use Viktorprogger\TelegramBot\Update\Update;

final class NotFoundException extends RuntimeException
{
    protected $message = 'No matches for the request';

    public function __construct(public readonly Update $request, int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($this->message, $code, $previous);
    }
}
