<?php

declare(strict_types=1);

namespace Botasis\Runtime\Handler;

use Botasis\Runtime\Response\Response;
use Botasis\Runtime\Response\ResponseInterface;
use Botasis\Runtime\Update\Update;
use Botasis\Runtime\UpdateHandlerInterface;

final class DummyUpdateHandler implements UpdateHandlerInterface
{
    public function handle(Update $update): ResponseInterface
    {
        return new Response($update);
    }
}
