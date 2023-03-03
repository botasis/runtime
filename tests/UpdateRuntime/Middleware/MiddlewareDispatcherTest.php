<?php

declare(strict_types=1);

namespace Botasis\Runtime\Tests\UpdateRuntime\Middleware;

use Botasis\Client\Telegram\Entity\CallbackResponse;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use RuntimeException;
use Botasis\Runtime\Entity\User\User;
use Botasis\Runtime\Entity\User\UserId;
use Botasis\Runtime\Update\UpdateId;
use Botasis\Runtime\Update\Update;
use Botasis\Runtime\Response\Response;
use Botasis\Runtime\Response\ResponseInterface;
use Botasis\Runtime\Tests\UpdateRuntime\Middleware\Support\FailMiddleware;
use Botasis\Runtime\Tests\UpdateRuntime\Middleware\Support\TestController;
use Botasis\Runtime\Tests\UpdateRuntime\Middleware\Support\TestMiddleware;
use Botasis\Runtime\UpdateRuntime\CallableFactory;
use Botasis\Runtime\UpdateRuntime\Middleware\Event\AfterMiddleware;
use Botasis\Runtime\UpdateRuntime\Middleware\Event\BeforeMiddleware;
use Botasis\Runtime\UpdateRuntime\Middleware\MiddlewareDispatcher;
use Botasis\Runtime\UpdateRuntime\Middleware\MiddlewareFactory;
use Botasis\Runtime\UpdateRuntime\RequestHandlerInterface;
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
                    return (new Response())->withCallbackResponse(new CallbackResponse('middleware-id'));
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

        $middleware1 = static function (Update $request, RequestHandlerInterface $handler): ResponseInterface {
            $request = $request->withAttribute('middleware', 'middleware1');

            return $handler->handle($request);
        };
        $middleware2 = static function (Update $request): ResponseInterface {
            $callbackResponse = new CallbackResponse($request->getAttribute('middleware'));

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
            $callbackResponse = new CallbackResponse('first');

            return (new Response())->withCallbackResponse($callbackResponse);
        };
        $middleware2 = static function (): ResponseInterface {
            $callbackResponse = new CallbackResponse('second');

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

        $middleware1 = static function (Update $request, RequestHandlerInterface $handler): ResponseInterface {
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
            public function handle(Update $update): ResponseInterface
            {
                return (new Response())->withCallbackResponse(new CallbackResponse('default-id'));
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

    private function getTelegramRequest(): Update
    {
        return new Update(
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
    }
}
