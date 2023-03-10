<?php

declare(strict_types=1);

namespace Botasis\Runtime\Middleware;

use Botasis\Runtime\Response\ResponseInterface;
use Botasis\Runtime\Update\Update;
use Botasis\Runtime\UpdateHandlerInterface;

interface MiddlewareInterface
{
    public function process(Update $request, UpdateHandlerInterface $handler): ResponseInterface;
}
