<?php

declare(strict_types=1);

namespace Botasis\Runtime\Tests\Middleware;

use Botasis\Client\Telegram\Request\CallbackResponse;
use Botasis\Runtime\CallableFactory;
use Botasis\Runtime\Entity\User\User;
use Botasis\Runtime\Middleware\Event\AfterMiddleware;
use Botasis\Runtime\Middleware\Event\BeforeMiddleware;
use Botasis\Runtime\Middleware\MiddlewareDispatcher;
use Botasis\Runtime\Middleware\MiddlewareFactory;
use Botasis\Runtime\Request\TelegramRequestEnriched;
use Botasis\Runtime\Response\Response;
use Botasis\Runtime\Response\ResponseInterface;
use Botasis\Runtime\Tests\Middleware\Support\FailMiddleware;
use Botasis\Runtime\Tests\Middleware\Support\TestController;
use Botasis\Runtime\Tests\Middleware\Support\TestMiddleware;
use Botasis\Runtime\Update\Update;
use Botasis\Runtime\Update\UpdateId;
use Botasis\Runtime\UpdateHandlerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use RuntimeException;
use Yiisoft\Test\Support\Container\SimpleContainer;
use Yiisoft\Test\Support\EventDispatcher\SimpleEventDispatcher;

final class MiddlewareDispatcherTest extends TestCase
{
    public function testCallableMiddlewareCalled(): void
    {
        $update = $this->getTelegramUpdate();

        $dispatcher = $this->createDispatcher()->withMiddlewares(
            static function () use ($update): ResponseInterface {
                return (new Response($update))->withRequest(new CallbackResponse('middleware-id'));
            },
        );

        $response = $dispatcher->dispatch($update, $this->getRequestHandler());
        $this->assertSame('middleware-id', $response->getRequests()[0]?->request->id);
    }

    public function testArrayMiddlewareCall(): void
    {
        $update = $this->getTelegramUpdate();
        $container = $this->createContainer(
            [
                TestController::class => new TestController(),
            ]
        );
        $dispatcher = $this->createDispatcher($container)->withMiddlewares([TestController::class, 'index']);

        $response = $dispatcher->dispatch($update, $this->getRequestHandler());
        $this->assertSame('test message', $response->getRequests()[0]?->request->text);
    }

    public function testMiddlewareFullStackCalled(): void
    {
        $update = $this->getTelegramUpdate();

        $middleware1 = static function (Update $request, UpdateHandlerInterface $handler): ResponseInterface {
            $request = $request->withAttribute('middleware', 'middleware1');

            return $handler->handle($request);
        };
        $middleware2 = static function (Update $update): ResponseInterface {
            $callbackResponse = new CallbackResponse($update->getAttribute('middleware'));

            return (new Response($update))->withRequest($callbackResponse);
        };

        $dispatcher = $this->createDispatcher()->withMiddlewares($middleware1, $middleware2);

        $response = $dispatcher->dispatch($update, $this->getRequestHandler());
        $this->assertSame('middleware1', $response->getRequests()[0]?->request->id);
    }

    public function testMiddlewareStackInterrupted(): void
    {
        $update = $this->getTelegramUpdate();

        $middleware1 = static function () use ($update): ResponseInterface {
            $callbackResponse = new CallbackResponse('first');

            return (new Response($update))->withRequest($callbackResponse);
        };
        $middleware2 = static function () use ($update): ResponseInterface {
            $callbackResponse = new CallbackResponse('second');

            return (new Response($update))->withRequest($callbackResponse);
        };

        $dispatcher = $this->createDispatcher()->withMiddlewares($middleware1, $middleware2);

        $response = $dispatcher->dispatch($update, $this->getRequestHandler());
        $this->assertSame('first', $response->getRequests()[0]?->request->id);
    }

    public function testEventsAreDispatched(): void
    {
        $eventDispatcher = new SimpleEventDispatcher();
        $update = $this->getTelegramUpdate();

        $middleware1 = static function (Update $request, UpdateHandlerInterface $handler): ResponseInterface {
            return $handler->handle($request);
        };
        $middleware2 = static function () use ($update): ResponseInterface {
            return new Response($update);
        };

        $dispatcher = $this->createDispatcher(null, $eventDispatcher)->withMiddlewares($middleware1, $middleware2);
        $dispatcher->dispatch($update, $this->getRequestHandler());

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

        $update = $this->getTelegramUpdate();
        $eventDispatcher = new SimpleEventDispatcher();
        $middleware = static fn(): FailMiddleware => new FailMiddleware();
        $dispatcher = $this->createDispatcher(null, $eventDispatcher)->withMiddlewares($middleware);

        try {
            $dispatcher->dispatch($update, $this->getRequestHandler());
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

    public static function dataHasMiddlewares(): array
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
            $this->createDispatcher()->withMiddlewares(...$definitions)->hasMiddlewares()
        );
    }

    public function testImmutability(): void
    {
        $dispatcher = $this->createDispatcher();
        self::assertNotSame($dispatcher, $dispatcher->withMiddlewares());
    }

    public function testResetStackOnWithMiddlewares(): void
    {
        $update = $this->getTelegramUpdate();
        $container = $this->createContainer(
            [
                TestController::class => new TestController(),
                TestMiddleware::class => new TestMiddleware(),
            ]
        );

        $dispatcher = $this
            ->createDispatcher($container)
            ->withMiddlewares([TestController::class, 'index']);
        $dispatcher->dispatch($update, $this->getRequestHandler());

        $dispatcher = $dispatcher->withMiddlewares(TestMiddleware::class);
        $response = $dispatcher->dispatch($update, $this->getRequestHandler());

        self::assertSame('42', $response->getRequests()[0]?->request->id);
    }

    private function getRequestHandler(): UpdateHandlerInterface
    {
        return new class () implements UpdateHandlerInterface {
            public function handle(Update $update): ResponseInterface
            {
                return (new Response($update))->withRequest(new CallbackResponse('default-id'));
            }
        };
    }

    private function createDispatcher(
        ?ContainerInterface $container = null,
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

    private function getTelegramUpdate(): Update
    {
        return new Update(
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
    }
}
