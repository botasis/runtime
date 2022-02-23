<?php

declare(strict_types=1);

namespace Viktorprogger\TelegramBot\Tests\Domain\UpdateRuntime\Middleware\Event;

use PHPUnit\Framework\TestCase;
use Viktorprogger\TelegramBot\Domain\Entity\User\User;
use Viktorprogger\TelegramBot\Domain\Entity\User\UserId;
use Viktorprogger\TelegramBot\Domain\UpdateRuntime\Middleware\Event\BeforeMiddleware;
use Viktorprogger\TelegramBot\Domain\UpdateRuntime\Middleware\MiddlewareInterface;
use Viktorprogger\TelegramBot\Domain\UpdateRuntime\TelegramRequest;

final class BeforeMiddlewareTest extends TestCase
{
    public function testGetMiddlewareAndRequest(): void
    {
        $middleware = $this->createMock(MiddlewareInterface::class);
        $request = new TelegramRequest('chatId', 'messageId', 'data', new User(new UserId('user-id')));

        $event = new BeforeMiddleware($middleware, $request);

        self::assertSame($middleware, $event->getMiddleware());
        self::assertSame($request, $event->getRequest());
    }
}
