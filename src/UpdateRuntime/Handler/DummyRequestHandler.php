<?php

declare(strict_types=1);

namespace Botasis\Runtime\UpdateRuntime\Handler;

use Botasis\Runtime\Update\Update;
use Botasis\Runtime\Response\Response;
use Botasis\Runtime\Response\ResponseInterface;
use Botasis\Runtime\UpdateRuntime\RequestHandlerInterface;

final class DummyRequestHandler implements RequestHandlerInterface
{
    public function handle(Update $update): ResponseInterface
    {
        return new Response();
    }
}
