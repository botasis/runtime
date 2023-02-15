<?php

declare(strict_types=1);

namespace Viktorprogger\TelegramBot\Tests\UpdateRuntime\Middleware;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use RuntimeException;
use Viktorprogger\TelegramBot\Request\RequestId;
use Viktorprogger\TelegramBot\Request\TelegramRequest;
use Viktorprogger\TelegramBot\Response\Response;
use Viktorprogger\TelegramBot\Response\ResponseInterface;
use Viktorprogger\TelegramBot\Response\TelegramCallbackResponse;
use Viktorprogger\TelegramBot\Tests\UpdateRuntime\Middleware\Support\FailMiddleware;
use Viktorprogger\TelegramBot\Tests\UpdateRuntime\Middleware\Support\TestController;
use Viktorprogger\TelegramBot\Tests\UpdateRuntime\Middleware\Support\TestMiddleware;
use Viktorprogger\TelegramBot\UpdateRuntime\CallableFactory;
use Viktorprogger\TelegramBot\UpdateRuntime\Middleware\Event\AfterMiddleware;
use Viktorprogger\TelegramBot\UpdateRuntime\Middleware\Event\BeforeMiddleware;
use Viktorprogger\TelegramBot\UpdateRuntime\Middleware\MiddlewareDispatcher;
use Viktorprogger\TelegramBot\UpdateRuntime\Middleware\MiddlewareFactory;
use Viktorprogger\TelegramBot\UpdateRuntime\RequestHandlerInterface;
use Viktorprogger\TelegramBot\User\User;
use Viktorprogger\TelegramBot\User\UserId;
use Yiisoft\Test\Support\Container\SimpleContainer;
use Yiisoft\Test\Support\EventDispatcher\SimpleEventDispatcher;

final class MiddlewareDispatcherTest extends TestCase
{
    public function testCallableMiddlewareCalled(): void
    {
        $request = $this->getTelegramRequest();

        $dispatcher = $this->createDispatcher()->withMiddlewares(
            [
                static function (): ResponseInterface {
                    return (new Response())->withCallbackResponse(new TelegramCallbackResponse('middleware-id'));
                },
            ]
        );

        $response = $dispatcher->dispatch($request, $this->getRequestHandler());
        $this->assertSame('middleware-id', $response->getCallbackResponse()?->id);
    }

    public function testArrayMiddlewareCall(): void
    {
        $request = $this->getTelegramRequest();
        $container = $this->createContainer(
            [
                TestController::class => new TestController(),
            ]
        );
        $dispatcher = $this->createDispatcher($container)->withMiddlewares([[TestController::class, 'index']]);

        $response = $dispatcher->dispatch($request, $this->getRequestHandler());
        $this->assertSame('test message', $response->getMessages()[0]?->text);
    }

    public function testMiddlewareFullStackCalled(): void
    {
        $request = $this->getTelegramRequest();

        $middleware1 = static function (TelegramRequest $request, RequestHandlerInterface $handler): ResponseInterface {
            $request = $request->withAttribute('middleware', 'middleware1');

            return $handler->handle($request);
        };
        $middleware2 = static function (TelegramRequest $request): ResponseInterface {
            $callbackResponse = new TelegramCallbackResponse($request->getAttribute('middleware'));

            return (new Response())->withCallbackResponse($callbackResponse);
        };

        $dispatcher = $this->createDispatcher()->withMiddlewares([$middleware1, $middleware2]);

        $response = $dispatcher->dispatch($request, $this->getRequestHandler());
        $this->assertSame('middleware1', $response->getCallbackResponse()?->id);
    }

    public function testMiddlewareStackInterrupted(): void
    {
        $request = $this->getTelegramRequest();

        $middleware1 = static function (): ResponseInterface {
            $callbackResponse = new TelegramCallbackResponse('first');

            return (new Response())->withCallbackResponse($callbackResponse);
        };
        $middleware2 = static function (): ResponseInterface {
            $callbackResponse = new TelegramCallbackResponse('second');

            return (new Response())->withCallbackResponse($callbackResponse);
        };

        $dispatcher = $this->createDispatcher()->withMiddlewares([$middleware1, $middleware2]);

        $response = $dispatcher->dispatch($request, $this->getRequestHandler());
        $this->assertSame('first', $response->getCallbackResponse()?->id);
    }

    public function testEventsAreDispatched(): void
    {
        $eventDispatcher = new SimpleEventDispatcher();
        $request = $this->getTelegramRequest();

        $middleware1 = static function (TelegramRequest $request, RequestHandlerInterface $handler): ResponseInterface {
            return $handler->handle($request);
        };
        $middleware2 = static function (): ResponseInterface {
            return new Response();
        };

        $dispatcher = $this->createDispatcher(null, $eventDispatcher)->withMiddlewares([$middleware1, $middleware2]);
        $dispatcher->dispatch($request, $this->getRequestHandler());

        $this->assertEquals(
            [
                BeforeMiddleware::class,
                BeforeMiddleware::class,
                AfterMiddleware::class,
                AfterMiddleware::class,
            ],
            $eventDispatcher->getEventClasses()
        );
    }

    public function testEventsAreDispatchedWhenMiddlewareFailedWithException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Middleware failed.');

        $request = $this->getTelegramRequest();
        $eventDispatcher = new SimpleEventDispatcher();
        $middleware = static fn(): FailMiddleware => new FailMiddleware();
        $dispatcher = $this->createDispatcher(null, $eventDispatcher)->withMiddlewares([$middleware]);

        try {
            $dispatcher->dispatch($request, $this->getRequestHandler());
        } finally {
            $this->assertEquals(
                [
                    BeforeMiddleware::class,
                    AfterMiddleware::class,
                ],
                $eventDispatcher->getEventClasses()
            );
        }
    }

    public function dataHasMiddlewares(): array
    {
        return [
            [[], false],
            [[[TestController::class, 'index']], true],
        ];
    }

    /**
     * @dataProvider dataHasMiddlewares
     */
    public function testHasMiddlewares(array $definitions, bool $expected): void
    {
        self::assertSame(
            $expected,
            $this->createDispatcher()->withMiddlewares($definitions)->hasMiddlewares()
        );
    }

    public function testImmutability(): void
    {
        $dispatcher = $this->createDispatcher();
        self::assertNotSame($dispatcher, $dispatcher->withMiddlewares([]));
    }

    public function testResetStackOnWithMiddlewares(): void
    {
        $request = $this->getTelegramRequest();
        $container = $this->createContainer(
            [
                TestController::class => new TestController(),
                TestMiddleware::class => new TestMiddleware(),
            ]
        );

        $dispatcher = $this
            ->createDispatcher($container)
            ->withMiddlewares([[TestController::class, 'index']]);
        $dispatcher->dispatch($request, $this->getRequestHandler());

        $dispatcher = $dispatcher->withMiddlewares([TestMiddleware::class]);
        $response = $dispatcher->dispatch($request, $this->getRequestHandler());

        self::assertSame('42', $response->getCallbackResponse()?->id);
    }

    private function getRequestHandler(): RequestHandlerInterface
    {
        return new class () implements RequestHandlerInterface {
            public function handle(TelegramRequest $request): ResponseInterface
            {
                return (new Response())->withCallbackResponse(new TelegramCallbackResponse('default-id'));
            }
        };
    }

    private function createDispatcher(
        ContainerInterface $container = null,
        ?EventDispatcherInterface $eventDispatcher = null
    ): MiddlewareDispatcher {
        $container = $container ?? $this->createContainer();
        $callableFactory = new CallableFactory($container);

        return new MiddlewareDispatcher(
            new MiddlewareFactory($container, $callableFactory),
            $eventDispatcher
        );
    }

    private function createContainer(array $instances = []): ContainerInterface
    {
        return new SimpleContainer($instances);
    }

    private function getTelegramRequest(): TelegramRequest
    {
        return new TelegramRequest(
            new RequestId(123),
            'chatId',
            'messageId',
            'data',
            new User(new UserId('user-id')),
            []
        );
    }
}
