<?php

declare(strict_types=1);

namespace Botasis\Runtime\Tests\UpdateRuntime\Middleware\Support;

use Psr\Container\NotFoundExceptionInterface;
use RuntimeException;

final class NotFoundException extends RuntimeException implements NotFoundExceptionInterface
{
}
