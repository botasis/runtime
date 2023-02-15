<?php

declare(strict_types=1);

namespace Viktorprogger\TelegramBot\Tests\UpdateRuntime\Middleware;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Viktorprogger\TelegramBot\Request\RequestId;
use Viktorprogger\TelegramBot\Request\TelegramRequest;
use Viktorprogger\TelegramBot\Response\Response;
use Viktorprogger\TelegramBot\Response\ResponseInterface;
use Viktorprogger\TelegramBot\Response\TelegramCallbackResponse;
use Viktorprogger\TelegramBot\Tests\UpdateRuntime\Middleware\Support\InvalidController;
use Viktorprogger\TelegramBot\Tests\UpdateRuntime\Middleware\Support\TestController;
use Viktorprogger\TelegramBot\Tests\UpdateRuntime\Middleware\Support\TestMiddleware;
use Viktorprogger\TelegramBot\Tests\UpdateRuntime\Middleware\Support\UseParamsController;
use Viktorprogger\TelegramBot\Tests\UpdateRuntime\Middleware\Support\UseParamsMiddleware;
use Viktorprogger\TelegramBot\UpdateRuntime\CallableFactory;
use Viktorprogger\TelegramBot\UpdateRuntime\InvalidCallableConfigurationException;
use Viktorprogger\TelegramBot\UpdateRuntime\Middleware\Exception\InvalidMiddlewareDefinitionException;
use Viktorprogger\TelegramBot\UpdateRuntime\Middleware\MiddlewareFactory;
use Viktorprogger\TelegramBot\UpdateRuntime\Middleware\MiddlewareFactoryInterface;
use Viktorprogger\TelegramBot\UpdateRuntime\Middleware\MiddlewareInterface;
use Viktorprogger\TelegramBot\UpdateRuntime\RequestHandlerInterface;
use Viktorprogger\TelegramBot\User\User;
use Viktorprogger\TelegramBot\User\UserId;
use Yiisoft\Test\Support\Container\SimpleContainer;

final class MiddlewareFactoryTest extends TestCase
{
    public function testCreateFromString(): void
    {
        $container = $this->getContainer([TestMiddleware::class => new TestMiddleware()]);
        $middleware = $this->getMiddlewareFactory($container)->create(TestMiddleware::class);
        self::assertInstanceOf(TestMiddleware::class, $middleware);
    }

    public function testCreateFromArray(): void
    {
        $container = $this->getContainer([TestController::class => new TestController()]);
        $middleware = $this->getMiddlewareFactory($container)->create([TestController::class, 'index']);
        self::assertSame(
            'test message',
            $middleware->process(
                $this->getTelegramRequest(),
                $this->createMock(RequestHandlerInterface::class)
            )->getMessages()[0]?->text
        );
    }

    public function testCreateFromClosureResponse(): void
    {
        $container = $this->getContainer([TestController::class => new TestController()]);
        $middleware = $this->getMiddlewareFactory($container)->create(
            static function (): ResponseInterface {
                return (new Response())->withCallbackResponse(new TelegramCallbackResponse('418'));
            }
        );
        self::assertSame(
            '418',
            $middleware->process(
                $this->getTelegramRequest(),
                $this->createMock(RequestHandlerInterface::class)
            )->getCallbackResponse()?->id
        );
    }

    public function testCreateFromClosureMiddleware(): void
    {
        $container = $this->getContainer([TestController::class => new TestController()]);
        $middleware = $this->getMiddlewareFactory($container)->create(
            static function (): MiddlewareInterface {
                return new TestMiddleware();
            }
        );
        self::assertSame(
            '42',
            $middleware->process(
                $this->getTelegramRequest(),
                $this->createMock(RequestHandlerInterface::class)
            )->getCallbackResponse()?->id
        );
    }

    public function testCreateWithUseParamsMiddleware(): void
    {
        $container = $this->getContainer([UseParamsMiddleware::class => new UseParamsMiddleware()]);
        $middleware = $this->getMiddlewareFactory($container)->create(UseParamsMiddleware::class);

        self::assertSame(
            'fake-id',
            $middleware->process(
                $this->getTelegramRequest(),
                $this->getRequestHandler()
            )->getCallbackResponse()?->id
        );
    }

    public function testCreateWithUseParamsController(): void
    {
        $container = $this->getContainer([UseParamsController::class => new UseParamsController()]);
        $middleware = $this->getMiddlewareFactory($container)->create([UseParamsController::class, 'index']);
        $request = $this->getTelegramRequest();

        self::assertSame(
            $request->chatId,
            $middleware->process(
                $request,
                $this->getRequestHandler()
            )->getMessages()[0]?->chatId
        );
    }

    public function testInvalidMiddlewareWithWrongCallable(): void
    {
        $container = $this->getContainer([TestController::class => new TestController()]);
        $middleware = $this->getMiddlewareFactory($container)->create(
            static function () {
                return 42;
            }
        );

        $this->expectException(InvalidMiddlewareDefinitionException::class);
        $middleware->process(
            $this->getTelegramRequest(),
            $this->createMock(RequestHandlerInterface::class)
        );
    }

    public function testInvalidMiddlewareWithWrongString(): void
    {
        $this->expectException(InvalidCallableConfigurationException::class);
        $this->getMiddlewareFactory()->create('test');
    }

    public function testInvalidMiddlewareWithWrongClass(): void
    {
        $this->expectException(InvalidCallableConfigurationException::class);
        $this->getMiddlewareFactory()->create(TestController::class);
    }

    public function testInvalidMiddlewareWithWrongController(): void
    {
        $container = $this->getContainer([InvalidController::class => new InvalidController()]);
        $middleware = $this->getMiddlewareFactory($container)->create([InvalidController::class, 'index']);

        $this->expectException(InvalidMiddlewareDefinitionException::class);
        $middleware->process(
            $this->getTelegramRequest(),
            $this->createMock(RequestHandlerInterface::class)
        );
    }

    public function testInvalidMiddlewareWithWrongArraySize(): void
    {
        $this->expectException(InvalidCallableConfigurationException::class);
        $this->getMiddlewareFactory()->create(['test']);
    }

    public function testInvalidMiddlewareWithWrongArrayClass(): void
    {
        $this->expectException(InvalidCallableConfigurationException::class);
        $this->getMiddlewareFactory()->create(['class', 'test']);
    }

    public function testInvalidMiddlewareWithWrongArrayType(): void
    {
        $this->expectException(InvalidCallableConfigurationException::class);
        $this->getMiddlewareFactory()->create(['class' => TestController::class, 'index']);
    }

    public function testInvalidMiddlewareWithWrongArrayWithIntItems(): void
    {
        $this->expectException(InvalidCallableConfigurationException::class);
        $this->getMiddlewareFactory()->create([7, 42]);
    }

    private function getMiddlewareFactory(ContainerInterface $container = null): MiddlewareFactoryInterface
    {
        $container = $container ?? $this->getContainer();

        return new MiddlewareFactory($container, new CallableFactory($container));
    }

    private function getContainer(array $instances = []): ContainerInterface
    {
        return new SimpleContainer($instances);
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
