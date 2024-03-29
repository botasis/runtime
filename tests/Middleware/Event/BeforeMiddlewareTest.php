<?php

declare(strict_types=1);

namespace Botasis\Runtime\Tests\Middleware\Event;

use Botasis\Runtime\Entity\User\User;
use Botasis\Runtime\Middleware\Event\BeforeMiddleware;
use Botasis\Runtime\Middleware\MiddlewareInterface;
use Botasis\Runtime\Update\Update;
use Botasis\Runtime\Update\UpdateId;
use PHPUnit\Framework\TestCase;

final class BeforeMiddlewareTest extends TestCase
{
    public function testGetMiddlewareAndRequest(): void
    {
        $middleware = $this->createMock(MiddlewareInterface::class);
        $request = new Update(
            new UpdateId(123),
            null,
            'messageId',
            'data',
            new User(
                'user-id',
                false,
                'testUser',
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
            ),
            []
        );

        $event = new BeforeMiddleware($middleware, $request);

        self::assertSame($middleware, $event->middleware);
        self::assertSame($request, $event->update);
    }
}
