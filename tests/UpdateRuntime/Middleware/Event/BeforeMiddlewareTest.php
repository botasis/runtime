<?php

declare(strict_types=1);

namespace Botasis\Runtime\Tests\UpdateRuntime\Middleware\Event;

use PHPUnit\Framework\TestCase;
use Botasis\Runtime\Entity\User\User;
use Botasis\Runtime\Entity\User\UserId;
use Botasis\Runtime\Update\UpdateId;
use Botasis\Runtime\Update\Update;
use Botasis\Runtime\UpdateRuntime\Middleware\Event\BeforeMiddleware;
use Botasis\Runtime\UpdateRuntime\Middleware\MiddlewareInterface;

final class BeforeMiddlewareTest extends TestCase
{
    public function testGetMiddlewareAndRequest(): void
    {
        $middleware = $this->createMock(MiddlewareInterface::class);
        $request = new Update(
            new UpdateId(123),
            'chatId',
            'messageId',
            'data',
            new User(
                new UserId('user-id'),
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
        self::assertSame($request, $event->request);
    }
}
