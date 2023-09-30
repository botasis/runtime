<?php

declare(strict_types=1);

namespace Botasis\Runtime\Tests\Middleware\Support;

use Botasis\Runtime\Middleware\MiddlewareInterface;
use Botasis\Runtime\Response\ResponseInterface;
use Botasis\Runtime\Update\Update;
use Botasis\Runtime\UpdateHandlerInterface;
use RuntimeException;

final class FailMiddleware implements MiddlewareInterface
{
    public function process(Update $update, UpdateHandlerInterface $handler): ResponseInterface
    {
        throw new RuntimeException('Middleware failed.');
    }
}
