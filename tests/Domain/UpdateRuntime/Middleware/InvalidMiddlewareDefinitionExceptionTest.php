<?php

declare(strict_types=1);

namespace Viktorprogger\TelegramBot\Tests\Domain\UpdateRuntime\Middleware;

use PHPUnit\Framework\TestCase;
use stdClass;
use Viktorprogger\TelegramBot\Domain\UpdateRuntime\Middleware\InvalidMiddlewareDefinitionException;
use Viktorprogger\TelegramBot\Tests\Domain\UpdateRuntime\Middleware\Support\TestController;

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
                'an instance of "Viktorprogger\TelegramBot\Tests\Domain\UpdateRuntime\Middleware\Support\TestController"',
            ],
            [
                [TestController::class, 'notExistsAction'],
                '["Viktorprogger\TelegramBot\Tests\Domain\UpdateRuntime\Middleware\Support\TestController", "notExistsAction"]',
            ],
            [
                ['class' => TestController::class, 'index'],
                '["class" => "Viktorprogger\TelegramBot\Tests\Domain\UpdateRuntime\Middleware\Support\TestController", "index"]',
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
