<?php

namespace Viktorprogger\TelegramBot\Domain\UpdateRuntime;

use Psr\Container\ContainerInterface;use Viktorprogger\TelegramBot\Domain\Action\ActionInterface;use Viktorprogger\TelegramBot\Domain\UpdateRuntime\NotFoundException;use Viktorprogger\TelegramBot\Domain\UpdateRuntime\TelegramRequest;

final class Router
{
    /**
     * @psalm-param list<array{rule: callable, action: class-string}>
     */
    private readonly array $routes;

    /**
     * @psalm-param $routes list<array{rule: callable, action: class-string}>
     */
    public function __construct(private readonly ContainerInterface $container, array $routes)
    {
        $this->routes = $routes;
    }

    public function match(TelegramRequest $request): ActionInterface
    {
        foreach ($this->routes as $route) {
            if ($route['rule']($request->requestData)) {
                return $this->container->get($route['action']);
            }
        }

        throw new NotFoundException($request);
    }
}
