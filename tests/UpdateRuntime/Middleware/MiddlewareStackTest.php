<?php

declare(strict_types=1);

namespace Viktorprogger\TelegramBot\Tests\UpdateRuntime\Middleware;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Viktorprogger\TelegramBot\Request\RequestId;
use Viktorprogger\TelegramBot\Request\TelegramRequest;
use Viktorprogger\TelegramBot\UpdateRuntime\Middleware\MiddlewareStack;
use Viktorprogger\TelegramBot\UpdateRuntime\RequestHandlerInterface;
use Viktorprogger\TelegramBot\User\User;
use Viktorprogger\TelegramBot\User\UserId;
use Yiisoft\Test\Support\EventDispatcher\SimpleEventDispatcher;

final class MiddlewareStackTest extends TestCase
{
    public function testHandleEmpty(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Stack is empty.');

        $request = new TelegramRequest(
            new RequestId(123),
            'chatId',
            'messageId',
            'data',
            new User(new UserId('user-id')),
            []
        );
        $stack = new MiddlewareStack([], $this->createMock(RequestHandlerInterface::class), new SimpleEventDispatcher());
        $stack->handle($request);
    }
}
