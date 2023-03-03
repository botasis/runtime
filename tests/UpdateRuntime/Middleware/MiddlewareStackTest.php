<?php

declare(strict_types=1);

namespace Viktorprogger\TelegramBot\Tests\UpdateRuntime\Middleware;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Viktorprogger\TelegramBot\Entity\User\User;
use Viktorprogger\TelegramBot\Entity\User\UserId;
use Viktorprogger\TelegramBot\Update\UpdateId;
use Viktorprogger\TelegramBot\Update\Update;
use Viktorprogger\TelegramBot\UpdateRuntime\Middleware\MiddlewareStack;
use Viktorprogger\TelegramBot\UpdateRuntime\RequestHandlerInterface;
use Yiisoft\Test\Support\EventDispatcher\SimpleEventDispatcher;

final class MiddlewareStackTest extends TestCase
{
    public function testHandleEmpty(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Stack is empty.');

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
        $stack = new MiddlewareStack([], $this->createMock(RequestHandlerInterface::class), new SimpleEventDispatcher()
        );
        $stack->handle($request);
    }
}
