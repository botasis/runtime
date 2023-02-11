<?php

namespace Viktorprogger\TelegramBot\Domain\UpdateRuntime;

use Psr\Container\ContainerInterface;
use Viktorprogger\TelegramBot\Domain\Entity\Request\TelegramRequest;

final readonly class Router
{
    /**
     * @psalm-param $routes list<array{rule: callable, action: class-string<RequestHandlerInterface>}>
     */
    public function __construct(private ContainerInterface $container, private array $routes)
    {
    }

    public function match(TelegramRequest $request): RequestHandlerInterface
    {
        foreach ($this->routes as $route) {
            if ($route['rule']($request->requestData)) {
                return $this->container->get($route['action']);
            }
        }

        throw new NotFoundException($request);
    }
}
