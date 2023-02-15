<?php

declare(strict_types=1);

namespace Viktorprogger\TelegramBot\Tests\UpdateRuntime\Middleware\Support;

use Psr\Container\NotFoundExceptionInterface;
use RuntimeException;

final class NotFoundException extends RuntimeException implements NotFoundExceptionInterface
{
}
