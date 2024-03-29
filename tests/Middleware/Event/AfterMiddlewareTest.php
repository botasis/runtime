<?php

declare(strict_types=1);

namespace Botasis\Runtime\Tests\Middleware\Event;

use Botasis\Runtime\Middleware\Event\AfterMiddleware;
use Botasis\Runtime\Middleware\MiddlewareInterface;
use Botasis\Runtime\Response\ResponseInterface;
use PHPUnit\Framework\TestCase;

final class AfterMiddlewareTest extends TestCase
{
    public function testGetMiddleware(): void
    {
        $middleware = $this->createMock(MiddlewareInterface::class);

        $event = new AfterMiddleware($middleware, null);

        self::assertSame($middleware, $event->middleware);
    }

    public function testGetResponse(): void
    {
        $middleware = $this->createMock(MiddlewareInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $event = new AfterMiddleware($middleware, $response);

        self::assertSame($response, $event->response);
    }

    public function testGetResponseNull(): void
    {
        $middleware = $this->createMock(MiddlewareInterface::class);

        $event = new AfterMiddleware($middleware, null);

        self::assertNull($event->response);
    }
}
