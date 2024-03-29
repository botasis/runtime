<?php

namespace Botasis\Runtime\Router;

use Botasis\Runtime\Update\Update;
use RuntimeException;
use Throwable;

final class NotFoundException extends RuntimeException
{
    protected $message = 'No matches for the request';

    public function __construct(public readonly Update $update, int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($this->message, $code, $previous);
    }
}
