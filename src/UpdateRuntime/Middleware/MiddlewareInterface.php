<?php

declare(strict_types=1);

namespace Botasis\Runtime\UpdateRuntime\Middleware;

use Botasis\Runtime\Update\Update;
use Botasis\Runtime\UpdateRuntime\RequestHandlerInterface;
use Botasis\Runtime\Response\ResponseInterface;

interface MiddlewareInterface
{
    public function process(Update $request, RequestHandlerInterface $handler): ResponseInterface;
}
