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
use Botasis\Runtime\Response\Response;
use Botasis\Runtime\Response\ResponseInterface;
use Botasis\Runtime\Router\Router;
use Botasis\Runtime\Update\Update;
use Botasis\Runtime\Update\UpdateId;
use Botasis\Runtime\UpdateHandlerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use function PHPUnit\Framework\assertEquals;

final class ApplicationTest extends TestCase
{
    public function testFullStack(): void
    {
        $updateHandler = new class implements UpdateHandlerInterface {
            public ?string $successCheck = null;

            public function handle(Update $update): ResponseInterface
            {
                $handler = $this;
                $message = new Message(
                    ($update->getAttribute('test') ?? '') . '4',
                    MessageFormat::TEXT,
                    'test',
                );
                return (new Response())
                    ->withRequest(
                        $message->onSuccess(function () use ($handler, $message) {
                            $handler->successCheck = $message->text . '5';
                        })
                    );
            }
        };
        $routes = [
            [
                Router::ROUTE_KEY_RULE => static fn() => true,
                Router::ROUTE_KEY_MIDDLEWARES => [
                    $this->getMiddleware('1'),
                ],
                Router::ROUTE_KEY_ROUTES_LIST => [
                    [
                        Router::ROUTE_KEY_RULE_STATIC => 'test',
                        Router::ROUTE_KEY_MIDDLEWARES => [
                            $this->getMiddleware('2'),
                        ],
                        Router::ROUTE_KEY_ROUTES_LIST => [
                            [
                                Router::ROUTE_KEY_RULE => static fn() => true,
                                Router::ROUTE_KEY_MIDDLEWARES => [
                                    $this->getMiddleware('3'),
                                ],
                                Router::ROUTE_KEY_ACTION => $updateHandler,
                            ],
                        ],
                    ],
                ]
            ],
        ];


        $body = $this->createMock(StreamInterface::class);
        $body->method('getContents')->willReturn('{"ok":true}');
        $httpResponse = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
        $httpResponse->method('getStatusCode')->willReturn(200);
        $httpResponse->method('getBody')->willReturn($body);
        $httpClient = $this->createMock(ClientInterface::class);
        $httpClient->method('sendRequest')->willReturn($httpResponse);


        $update = new Update(new UpdateId(1), '1', null, '1', 'test', null, []);
        $container = $this->createMock(ContainerInterface::class);

        $middlewareDispatcher = new MiddlewareDispatcher(
            new MiddlewareFactory(
                $container,
                new CallableFactory($container)
            ),
            $this->createMock(EventDispatcherInterface::class),
        );

        $apiRequest = $this->createMock(RequestInterface::class);
        $apiRequest->method('withHeader')->willReturn($apiRequest);
        $apiRequest->method('withBody')->willReturn($apiRequest);

        $requestFactory = $this->createMock(RequestFactoryInterface::class);
        $requestFactory->method('createRequest')->willReturn($apiRequest);

        $response = (new Application(
            new Emitter(new ClientPsr(
                'token',
                $httpClient,
                $requestFactory,
                $this->createMock(StreamFactoryInterface::class),
            )),
            $this->createMock(UpdateHandlerInterface::class),
            $middlewareDispatcher->withMiddlewares(
                new RouterMiddleware(
                    new Router(
                        $container,
                        $middlewareDispatcher,
                        $routes,
                    )
                )
            ),
        ))->handle($update);

        assertEquals('1234321', $response->getRequests()[0]?->text);
        assertEquals('12345', $updateHandler->successCheck);
    }

    public function getMiddleware(string $addition): MiddlewareInterface
    {
        return new class ($addition) implements MiddlewareInterface {
            public function __construct(private readonly string $addition)
            {
            }

            public function process(Update $request, UpdateHandlerInterface $handler): ResponseInterface
            {
                $response = $handler->handle(
                    $request->withAttribute(
                        'test',
                        ($request->getAttribute('test') ?? '') . $this->addition,
                    )
                );

                /** @var Message $message */
                $message = $response->getRequests()[0];

                return $response->withRequestReplaced($message, $message->withText($message->text . $this->addition));
            }
        };
    }
}
