<?php

declare(strict_types=1);

namespace Botasis\Runtime\Tests\UpdateRuntime\Middleware;

use Botasis\Client\Telegram\Entity\CallbackResponse;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Botasis\Runtime\Entity\User\User;
use Botasis\Runtime\Entity\User\UserId;
use Botasis\Runtime\Update\UpdateId;
use Botasis\Runtime\Update\Update;
use Botasis\Runtime\Response\Response;
use Botasis\Runtime\Response\ResponseInterface;
use Botasis\Runtime\Tests\UpdateRuntime\Middleware\Support\InvalidController;
use Botasis\Runtime\Tests\UpdateRuntime\Middleware\Support\TestController;
use Botasis\Runtime\Tests\UpdateRuntime\Middleware\Support\TestMiddleware;
use Botasis\Runtime\Tests\UpdateRuntime\Middleware\Support\UseParamsController;
use Botasis\Runtime\Tests\UpdateRuntime\Middleware\Support\UseParamsMiddleware;
use Botasis\Runtime\UpdateRuntime\CallableFactory;
use Botasis\Runtime\UpdateRuntime\InvalidCallableConfigurationException;
use Botasis\Runtime\UpdateRuntime\Middleware\Exception\InvalidMiddlewareDefinitionException;
use Botasis\Runtime\UpdateRuntime\Middleware\MiddlewareFactory;
use Botasis\Runtime\UpdateRuntime\Middleware\MiddlewareFactoryInterface;
use Botasis\Runtime\UpdateRuntime\Middleware\MiddlewareInterface;
use Botasis\Runtime\UpdateRuntime\RequestHandlerInterface;
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
                $this->getTelegramUpdate(),
                $this->createMock(RequestHandlerInterface::class)
            )->getMessages()[0]?->text
        );
    }

    public function testCreateFromClosureResponse(): void
    {
        $container = $this->getContainer([TestController::class => new TestController()]);
        $middleware = $this->getMiddlewareFactory($container)->create(
            static function (): ResponseInterface {
                return (new Response())->withCallbackResponse(new CallbackResponse('418'));
            }
        );
        self::assertSame(
            '418',
            $middleware->process(
                $this->getTelegramUpdate(),
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
                $this->getTelegramUpdate(),
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
                $this->getTelegramUpdate(),
                $this->getRequestHandler()
            )->getCallbackResponse()?->id
        );
    }

    public function testCreateWithUseParamsController(): void
    {
        $container = $this->getContainer([UseParamsController::class => new UseParamsController()]);
        $middleware = $this->getMiddlewareFactory($container)->create([UseParamsController::class, 'index']);
        $request = $this->getTelegramUpdate();

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
            $this->getTelegramUpdate(),
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
            $this->getTelegramUpdate(),
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
            public function handle(Update $update): ResponseInterface
            {
                return (new Response())->withCallbackResponse(new CallbackResponse('default-id'));
            }
        };
    }

    private function getTelegramUpdate(): Update
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
