<?php

declare(strict_types=1);

namespace Botasis\Runtime\Tests\UpdateRuntime\Middleware\Support;

use RuntimeException;
use Botasis\Runtime\Update\Update;
use Botasis\Runtime\Response\ResponseInterface;
use Botasis\Runtime\UpdateRuntime\Middleware\MiddlewareInterface;
use Botasis\Runtime\UpdateRuntime\RequestHandlerInterface;

final class FailMiddleware implements MiddlewareInterface
{
    public function process(Update $request, RequestHandlerInterface $handler): ResponseInterface
    {
        throw new RuntimeException('Middleware failed.');
    }
}
