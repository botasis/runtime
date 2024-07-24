<?php

declare(strict_types=1);

namespace Botasis\Runtime\Tests\Router;

use Botasis\Client\Telegram\Request\Message\Message;
use Botasis\Client\Telegram\Request\Message\MessageFormat;
use Botasis\Runtime\CallableFactory;
use Botasis\Runtime\Middleware\MiddlewareDispatcher;
use Botasis\Runtime\Middleware\MiddlewareFactory;
use Botasis\Runtime\Middleware\MiddlewareInterface;
use Botasis\Runtime\Request\TelegramRequestDecorator;
use Botasis\Runtime\Response\Response;
use Botasis\Runtime\Response\ResponseInterface;
use Botasis\Runtime\Router\CallableResolver;
use Botasis\Runtime\Router\Exception\InvalidActionReturnTypeException;
use Botasis\Runtime\Router\Exception\InvalidRuleReturnTypeException;
use Botasis\Runtime\Router\Group;
use Botasis\Runtime\Router\Route;
use Botasis\Runtime\Router\Router;
use Botasis\Runtime\Router\RuleDynamic;
use Botasis\Runtime\Router\RuleStatic;
use Botasis\Runtime\Update\Update;
use Botasis\Runtime\Update\UpdateId;
use Botasis\Runtime\UpdateHandlerInterface;
use PHPUnit\Framework\TestCase;
use Yiisoft\Injector\Injector;
use Yiisoft\Test\Support\Container\SimpleContainer;
use Yiisoft\Test\Support\EventDispatcher\SimpleEventDispatcher;

use function PHPUnit\Framework\assertEquals;

final class RouterTest extends TestCase
{
    public function testMiddlewareStackInGroup(): void
    {
        $routes = [
            (new Group(
                new RuleDynamic(static fn() => true),
                (new Group(
                    new RuleStatic('test'),
                    (new Route(
                        new RuleDynamic(static fn() => true),
                        new class {
                            public function __invoke(Update $update): ResponseInterface
                            {
                                return (new Response($update))
                                    ->withRequest(
                                        new TelegramRequestDecorator(
                                            new Message(
                                                ($update->getAttribute('test') ?? '') . '4',
                                                MessageFormat::TEXT,
                                                'test',
                                            ),
                                        ),
                                    );
                            }
                        },
                    ))->withMiddlewares($this->getMiddleware('3')),
                ))->withMiddlewares($this->getMiddleware('2')),
            ))->withMiddlewares($this->getMiddleware('1')),
        ];
        $router = $this->getRouter($routes);
        $update = new Update(new UpdateId(1), null, '1', 'test', null, []);
        $response = $router->match($update)->handle($update);

        assertEquals('1234', $response->getRequests()[0]?->request->text);
    }

    public function testInvalidRuleReturnTypeException(): void
    {
        $routes = [
            'group-1' => new Group(
                new RuleDynamic(static fn() => true),
                ...[
                    'group-2' => new Group(
                        new RuleDynamic(static fn() => 123),
                    )
                ],
            ),
        ];
        $router = $this->getRouter($routes);
        $update = new Update(new UpdateId(1), null, '1', 'test', null, []);

        $this->expectException(InvalidRuleReturnTypeException::class);
        $this->expectExceptionMessage('Invalid rule return type in route "group-1" => "group-2". Expected boolean, int given.');
        $router->match($update)->handle($update);
    }

    public function testInvalidActionReturnTypeException(): void
    {
        $routes = [
            'group-1' => new Group(
                new RuleDynamic(static fn() => true),
                ...[
                    'group-2' => new Group(
                        new RuleDynamic(static fn() => true),
                        ...[
                            'route-key' => new Route(
                                new RuleStatic('test'),
                                static fn() => 123,
                            )
                        ]
                    )
                ],
            ),
        ];
        $router = $this->getRouter($routes);
        $update = new Update(new UpdateId(1), null, '1', 'test', null, []);

        $this->expectException(InvalidActionReturnTypeException::class);
        $this->expectExceptionMessage('Invalid action return type in route "group-1" => "group-2" => "route-key". Expected null|Botasis\Runtime\Response\ResponseInterface, int given.');
        $router->match($update)->handle($update);
    }

    private function getRouter(array $routes): Router
    {
        $container = new SimpleContainer();
        $callableFactory = new CallableFactory($container);
        $injector = new Injector($container);
        $callableResolver = new CallableResolver($callableFactory, $injector);

        return new Router(
            $container,
            new MiddlewareDispatcher(
                new MiddlewareFactory($container, $callableFactory),
                new SimpleEventDispatcher(),
            ),
            $callableResolver,
            ...$routes,
        );
    }

    private function getMiddleware(string $addition): MiddlewareInterface
    {
        return new class ($addition) implements MiddlewareInterface {
            public function __construct(private readonly string $addition)
            {
            }

            public function process(Update $update, UpdateHandlerInterface $handler): ResponseInterface
            {
                return $handler->handle(
                    $update->withAttribute(
                        'test',
                        ($update->getAttribute('test') ?? '') . $this->addition,
                    )
                );
            }
        };
    }
}