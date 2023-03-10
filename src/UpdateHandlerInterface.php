<?php

declare(strict_types=1);

namespace Botasis\Runtime;

use Botasis\Runtime\Response\ResponseInterface;
use Botasis\Runtime\Update\Update;

interface UpdateHandlerInterface
{
    /**
     * Handles a request and produces a response.
     *
     * May call other collaborating code to generate the response.
     */
    public function handle(Update $update): ResponseInterface;
}
