<?php

declare(strict_types=1);

namespace Botasis\Runtime\Tests\Middleware;

use Botasis\Client\Telegram\Request\CallbackResponse;
use Botasis\Runtime\CallableFactory;
use Botasis\Runtime\Entity\User\User;
use Botasis\Runtime\Entity\User\UserId;
use Botasis\Runtime\InvalidCallableConfigurationException;
use Botasis\Runtime\Middleware\Exception\InvalidMiddlewareDefinitionException;
use Botasis\Runtime\Middleware\MiddlewareFactory;
use Botasis\Runtime\Middleware\MiddlewareFactoryInterface;
use Botasis\Runtime\Middleware\MiddlewareInterface;
use Botasis\Runtime\Response\Response;
use Botasis\Runtime\Response\ResponseInterface;
use Botasis\Runtime\Tests\Middleware\Support\InvalidController;
use Botasis\Runtime\Tests\Middleware\Support\TestController;
use Botasis\Runtime\Tests\Middleware\Support\TestMiddleware;
use Botasis\Runtime\Tests\Middleware\Support\UseParamsController;
use Botasis\Runtime\Tests\Middleware\Support\UseParamsMiddleware;
use Botasis\Runtime\Update\Chat;
use Botasis\Runtime\Update\ChatType;
use Botasis\Runtime\Update\Update;
use Botasis\Runtime\Update\UpdateId;
use Botasis\Runtime\UpdateHandlerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
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
                $this->createTelegramUpdate(),
                $this->createMock(UpdateHandlerInterface::class)
            )->getRequests()[0]?->text
        );
    }

    public function testCreateFromClosureResponse(): void
    {
        $container = $this->getContainer([TestController::class => new TestController()]);
        $update = $this->createTelegramUpdate();
        $middleware = $this->getMiddlewareFactory($container)->create(
            static function () use ($update): ResponseInterface {
                return (new Response($update))->withRequest(new CallbackResponse('418'));
            }
        );
        self::assertSame(
            '418',
            $middleware->process(
                $update,
                $this->createMock(UpdateHandlerInterface::class)
            )->getRequests()[0]?->id
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
                $this->createTelegramUpdate(),
                $this->createMock(UpdateHandlerInterface::class)
            )->getRequests()[0]?->id
        );
    }

    public function testCreateWithUseParamsMiddleware(): void
    {
        $container = $this->getContainer([UseParamsMiddleware::class => new UseParamsMiddleware()]);
        $middleware = $this->getMiddlewareFactory($container)->create(UseParamsMiddleware::class);

        self::assertSame(
            'fake-id',
            $middleware->process(
                $this->createTelegramUpdate(),
                $this->getRequestHandler()
            )->getRequests()[0]?->id
        );
    }

    public function testCreateWithUseParamsController(): void
    {
        $container = $this->getContainer([UseParamsController::class => new UseParamsController()]);
        $middleware = $this->getMiddlewareFactory($container)->create([UseParamsController::class, 'index']);
        $update = $this->createTelegramUpdate();

        /** Key "0" contains a {@see CallbackResponse} */
        self::assertSame(
            $update->chat->id,
            $middleware->process(
                $update,
                $this->getRequestHandler()
            )->getRequests()[1]?->chatId
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
            $this->createTelegramUpdate(),
            $this->createMock(UpdateHandlerInterface::class)
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
            $this->createTelegramUpdate(),
            $this->createMock(UpdateHandlerInterface::class)
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

    private function getRequestHandler(): UpdateHandlerInterface
    {
        return new class () implements UpdateHandlerInterface {
            public function handle(Update $update): ResponseInterface
            {
                return (new Response($update))->withRequest(new CallbackResponse('default-id'));
            }
        };
    }

    private function createTelegramUpdate(): Update
    {
        return new Update(
            new UpdateId(123),
            new Chat(
                'chat-id',
                ChatType::PRIVATE,
                null,
                null,
                null,
                null,
                null,
                null,
                [],
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                [],
            ),
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
