<?php

declare(strict_types=1);

namespace Viktorprogger\TelegramBot\Tests\Domain\UpdateRuntime\Middleware;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Viktorprogger\TelegramBot\Domain\Entity\User\User;
use Viktorprogger\TelegramBot\Domain\Entity\User\UserId;
use Viktorprogger\TelegramBot\Domain\UpdateRuntime\Middleware\MiddlewareStack;
use Viktorprogger\TelegramBot\Domain\UpdateRuntime\RequestHandlerInterface;
use Viktorprogger\TelegramBot\Domain\UpdateRuntime\TelegramRequest;
use Yiisoft\Test\Support\EventDispatcher\SimpleEventDispatcher;

final class MiddlewareStackTest extends TestCase
{
    public function testHandleEmpty(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Stack is empty.');

        $request = new TelegramRequest('chatId', 'messageId', 'data', new User(new UserId('user-id')));
        $stack = new MiddlewareStack([], $this->createMock(RequestHandlerInterface::class), new SimpleEventDispatcher());
        $stack->handle($request);
    }
}
