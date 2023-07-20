<?php

declare(strict_types=1);

namespace Botasis\Runtime\Tests;

use Botasis\Client\Telegram\Request\Message\Message;
use Botasis\Client\Telegram\Request\Message\MessageFormat;
use Botasis\Runtime\CallableFactory;
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
use Yiisoft\Test\Support\Container\SimpleContainer;
use Yiisoft\Test\Support\EventDispatcher\SimpleEventDispatcher;

use function PHPUnit\Framework\assertEquals;

final class RouterTest extends TestCase
{
    public function testMiddlewareStackInGroup(): void
    {
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
                                Router::ROUTE_KEY_ACTION => new class implements UpdateHandlerInterface {
                                    public function handle(Update $update): ResponseInterface
                                    {
                                        return (new Response())
                                            ->withRequest(
                                                new Message(
                                                    ($update->getAttribute('test') ?? '') . '4',
                                                    MessageFormat::TEXT,
                                                    'test',
                                                )
                                            );
                                    }
                                },
                            ],
                        ],
                    ],
                ]
            ],
        ];
        $router = $this->getRouter($routes);
        $update = new Update(new UpdateId(1), null, '1', 'test', null, []);
        $response = $router->match($update)->handle($update);

        assertEquals('1234', $response->getRequests()[0]?->text);
    }

    public function getRouter(array $routes): Router
    {
        $container = new SimpleContainer();

        return new Router(
            $container,
            new MiddlewareDispatcher(
                new MiddlewareFactory($container, new CallableFactory($container)),
                new SimpleEventDispatcher(),
            ),
            $routes,
        );
    }

    public function getMiddleware(string $addition): MiddlewareInterface
    {
        return new class ($addition) implements MiddlewareInterface {
            public function __construct(private readonly string $addition)
            {
            }

            public function process(Update $request, UpdateHandlerInterface $handler): ResponseInterface
            {
                return $handler->handle(
                    $request->withAttribute(
                        'test',
                        ($request->getAttribute('test') ?? '') . $this->addition,
                    )
                );
            }
        };
    }
}
