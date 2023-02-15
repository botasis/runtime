<?php

declare(strict_types=1);

namespace Viktorprogger\TelegramBot\Tests\UpdateRuntime\Middleware\Event;

use PHPUnit\Framework\TestCase;
use Viktorprogger\TelegramBot\Request\RequestId;
use Viktorprogger\TelegramBot\Request\TelegramRequest;
use Viktorprogger\TelegramBot\UpdateRuntime\Middleware\Event\BeforeMiddleware;
use Viktorprogger\TelegramBot\UpdateRuntime\Middleware\MiddlewareInterface;
use Viktorprogger\TelegramBot\User\User;
use Viktorprogger\TelegramBot\User\UserId;

final class BeforeMiddlewareTest extends TestCase
{
    public function testGetMiddlewareAndRequest(): void
    {
        $middleware = $this->createMock(MiddlewareInterface::class);
        $request = new TelegramRequest(
            new RequestId(123),
            'chatId',
            'messageId',
            'data',
            new User(new UserId('user-id')),
            []
        );

        $event = new BeforeMiddleware($middleware, $request);

        self::assertSame($middleware, $event->middleware);
        self::assertSame($request, $event->request);
    }
}
