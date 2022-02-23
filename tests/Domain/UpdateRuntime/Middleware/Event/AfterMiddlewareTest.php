<?php

declare(strict_types=1);

namespace Viktorprogger\TelegramBot\Tests\Domain\UpdateRuntime\Middleware\Event;

use PHPUnit\Framework\TestCase;
use Viktorprogger\TelegramBot\Domain\Client\ResponseInterface;
use Viktorprogger\TelegramBot\Domain\UpdateRuntime\Middleware\Event\AfterMiddleware;
use Viktorprogger\TelegramBot\Domain\UpdateRuntime\Middleware\MiddlewareInterface;

final class AfterMiddlewareTest extends TestCase
{
    public function testGetMiddleware(): void
    {
        $middleware = $this->createMock(MiddlewareInterface::class);

        $event = new AfterMiddleware($middleware, null);

        self::assertSame($middleware, $event->getMiddleware());
    }

    public function testGetResponse(): void
    {
        $middleware = $this->createMock(MiddlewareInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $event = new AfterMiddleware($middleware, $response);

        self::assertSame($response, $event->getResponse());
    }

    public function testGetResponseNull(): void
    {
        $middleware = $this->createMock(MiddlewareInterface::class);

        $event = new AfterMiddleware($middleware, null);

        self::assertNull($event->getResponse());
    }
}
