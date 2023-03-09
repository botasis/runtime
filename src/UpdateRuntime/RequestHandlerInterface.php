<?php

declare(strict_types=1);

namespace Botasis\Runtime\UpdateRuntime;

use Botasis\Runtime\Update\Update;
use Botasis\Runtime\Response\ResponseInterface;

interface RequestHandlerInterface
{
    /**
     * Handles a request and produces a response.
     *
     * May call other collaborating code to generate the response.
     */
    public function handle(Update $update): ResponseInterface;
}
