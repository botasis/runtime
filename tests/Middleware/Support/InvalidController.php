<?php

declare(strict_types=1);

namespace Botasis\Runtime\Tests\Middleware\Support;

final class InvalidController
{
    public function index(): int
    {
        return 200;
    }
}
