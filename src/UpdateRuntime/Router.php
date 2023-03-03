<?php

namespace Viktorprogger\TelegramBot\UpdateRuntime;

use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use Viktorprogger\TelegramBot\Update\Update;

final readonly class Router
{
    public const ROUTE_KEY_RULE_STATIC = 'rule_static';
    public const ROUTE_KEY_RULE = 'rule';
    public const ROUTE_KEY_ACTION = 'action';
    private array $rulesStatic;

    /**
     * @psalm-param $routes list<array{rule: callable, action: class-string<RequestHandlerInterface>}>
     */
    public function __construct(private ContainerInterface $container, private array $routes)
    {
        $rulesStatic = [];
        foreach ($this->routes as $route) {
            $staticRule = $this->getRuleStatic($route);
            $hasStaticRule = $staticRule !== [];
            if (!$hasStaticRule xor isset($route['rule'])) {
                throw new InvalidArgumentException('Telegram route must have either "rule_static" or "rule" key.');
            }

            if ($hasStaticRule) {
                $rulesStatic[$staticRule[0]] = $staticRule[1];
            }
        }

        $this->rulesStatic = $rulesStatic;
    }

    public function match(Update $request): RequestHandlerInterface
    {
        if (isset($this->rulesStatic[$request->requestData])) {
            return $this->container->get($this->rulesStatic[$request->requestData]); // TODO validate action
        }

        foreach ($this->routes as $route) {
            if ($route['rule']($request->requestData)) {
                return $this->container->get($route['action']); // TODO validate action
            }
        }

        throw new NotFoundException($request);
    }

    private function getRuleStatic(array $route): array
    {
        if (isset($route[self::ROUTE_KEY_RULE_STATIC])) {
            $rule = $route[self::ROUTE_KEY_RULE_STATIC];
            if (is_string($rule) && $rule !== '') {
                return [$rule, $route[self::ROUTE_KEY_ACTION]]; // TODO validate action
            }

            $key = self::ROUTE_KEY_RULE_STATIC;
            throw new InvalidArgumentException("Telegram route key $key must contain non-empty string");
        }

        return [];
    }
}
