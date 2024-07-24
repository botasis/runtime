<?php

declare(strict_types=1);

namespace Botasis\Runtime\Tests;

use Botasis\Client\Telegram\Client\ClientPsr;
use Botasis\Client\Telegram\Request\Message\Message;
use Botasis\Client\Telegram\Request\Message\MessageFormat;
use Botasis\Runtime\Application;
use Botasis\Runtime\CallableFactory;
use Botasis\Runtime\Emitter;
use Botasis\Runtime\Middleware\Implementation\RouterMiddleware;
use Botasis\Runtime\Middleware\MiddlewareDispatcher;
use Botasis\Runtime\Middleware\MiddlewareFactory;
use Botasis\Runtime\Middleware\MiddlewareInterface;
use Botasis\Runtime\Request\TelegramRequestDecorator;
use Botasis\Runtime\Response\Response;
use Botasis\Runtime\Response\ResponseInterface;
use Botasis\Runtime\Router\CallableResolver;
use Botasis\Runtime\Router\Group;
use Botasis\Runtime\Router\Route;
use Botasis\Runtime\Router\Router;
use Botasis\Runtime\Router\RuleDynamic;
use Botasis\Runtime\Router\RuleStatic;
use Botasis\Runtime\Update\Update;
use Botasis\Runtime\Update\UpdateId;
use Botasis\Runtime\UpdateHandlerInterface;
use Http\Message\MultipartStream\MultipartStreamBuilder;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Yiisoft\Injector\Injector;

use function PHPUnit\Framework\assertEquals;

final class ApplicationTest extends TestCase
{
    public function testFullStack(): void
    {
        $updateHandler = new class {
            public ?string $successCheck = null;

            public function handle(Update $update): ResponseInterface
            {
                $handler = $this;
                $message = new Message(
                    ($update->getAttribute('test') ?? '') . '4',
                    MessageFormat::TEXT,
                    'test',
                );
                return (new Response($update))
                    ->withRequest(
                        new TelegramRequestDecorator(
                            $message->onSuccess(function () use ($handler, $message) {
                                $handler->successCheck = $message->text . '5';
                            }),
                        ),
                    );
            }
        };
        $routes = [
            (new Group(
                new RuleDynamic(static fn() => true),
                (new Group(
                    new RuleStatic('test'),
                    (new Route(
                        new RuleDynamic(static fn() => true),
                        [$updateHandler, 'handle'],
                    ))->withMiddlewares($this->getMiddleware('3')),
                ))->withMiddlewares($this->getMiddleware('2')),
            ))->withMiddlewares($this->getMiddleware('1')),
        ];

        $body = $this->createMock(StreamInterface::class);
        $body->method('getContents')->willReturn('{"ok":true}');
        $httpResponse = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
        $httpResponse->method('getStatusCode')->willReturn(200);
        $httpResponse->method('getBody')->willReturn($body);
        $httpClient = $this->createMock(ClientInterface::class);
        $httpClient->method('sendRequest')->willReturn($httpResponse);


        $update = new Update(new UpdateId(1), null, '1', 'test', null, []);
        $container = $this->createMock(ContainerInterface::class);
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $callableFactory = new CallableFactory($container);

        $middlewareDispatcher = new MiddlewareDispatcher(
            new MiddlewareFactory(
                $container,
                $callableFactory
            ),
            $eventDispatcher,
        );

        $injector = new Injector($container);
        $callableResolver = new CallableResolver($callableFactory, $injector);

        $apiRequest = $this->createMock(RequestInterface::class);
        $apiRequest->method('withHeader')->willReturn($apiRequest);
        $apiRequest->method('withBody')->willReturn($apiRequest);

        $requestFactory = $this->createMock(RequestFactoryInterface::class);
        $requestFactory->method('createRequest')->willReturn($apiRequest);

        $streamFactory = $this->createMock(StreamFactoryInterface::class);
        $response = (new Application(
            new Emitter(
                new ClientPsr(
                    'token',
                    $httpClient,
                    $requestFactory,
                    $streamFactory,
                    new MultipartStreamBuilder($streamFactory)
                ),
                $eventDispatcher,
            ),
            $this->createMock(UpdateHandlerInterface::class),
            $middlewareDispatcher->withMiddlewares(
                new RouterMiddleware(
                    new Router(
                        $container,
                        $middlewareDispatcher,
                        $callableResolver,
                        ...$routes,
                    )
                )
            ),
        ))->handle($update);

        assertEquals('1234321', $response->getRequests()[0]?->request->text);
        assertEquals('12345', $updateHandler->successCheck);
    }

    public function getMiddleware(string $addition): MiddlewareInterface
    {
        return new class ($addition) implements MiddlewareInterface {
            public function __construct(private readonly string $addition)
            {
            }

            public function process(Update $update, UpdateHandlerInterface $handler): ResponseInterface
            {
                $response = $handler->handle(
                    $update->withAttribute(
                        'test',
                        ($update->getAttribute('test') ?? '') . $this->addition,
                    )
                );

                $message = $response->getRequests()[0];

                return $response->withRequestReplaced($message->request, new TelegramRequestDecorator($message->request->withText($message->request->text . $this->addition)));
            }
        };
    }
}
