<?php

declare(strict_types=1);

namespace Botasis\Runtime\Tests\UpdateRuntime\Middleware;

use PHPUnit\Framework\TestCase;
use stdClass;
use Botasis\Runtime\Tests\UpdateRuntime\Middleware\Support\TestController;
use Botasis\Runtime\UpdateRuntime\Middleware\Exception\InvalidMiddlewareDefinitionException;

final class InvalidMiddlewareDefinitionExceptionTest extends TestCase
{
    public function dataBase(): array
    {
        return [
            [
                'test',
                '"test"',
            ],
            [
                new TestController(),
                'an instance of "Botasis\Runtime\Tests\UpdateRuntime\Middleware\Support\TestController"',
            ],
            [
                [TestController::class, 'notExistsAction'],
                '["Botasis\Runtime\Tests\UpdateRuntime\Middleware\Support\TestController", "notExistsAction"]',
            ],
            [
                ['class' => TestController::class, 'index'],
                '["class" => "Botasis\Runtime\Tests\UpdateRuntime\Middleware\Support\TestController", "index"]',
            ],
        ];
    }

    /**
     * @dataProvider dataBase
     *
     * @param mixed $definition
     * @param string $expected
     */
    public function testBase(mixed $definition, string $expected): void
    {
        $exception = new InvalidMiddlewareDefinitionException($definition);
        self::assertStringEndsWith('. Got ' . $expected . '.', $exception->getMessage());
    }

    public function dataUnknownDefinition(): array
    {
        return [
            [42],
            [[new stdClass()]],
        ];
    }

    /**
     * @dataProvider dataUnknownDefinition
     *
     * @param mixed $definition
     */
    public function testUnknownDefinition(mixed $definition): void
    {
        $exception = new InvalidMiddlewareDefinitionException($definition);
        self::assertSame(
            'Parameter should be either PSR middleware class name or a callable.',
            $exception->getMessage()
        );
    }
}
