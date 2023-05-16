<?php

namespace Botasis\Runtime;

use Botasis\Runtime\Update\Update;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;

final readonly class Router
{
    public const ROUTE_KEY_RULE_STATIC = 'rule_static';
    public const ROUTE_KEY_RULE = 'rule';
    public const ROUTE_KEY_ACTION = 'action';
    private array $rulesStatic;

    /**
     * @psalm-param $routes list<array{rule: callable, action: class-string<UpdateHandlerInterface>}>
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

    public function match(Update $update): UpdateHandlerInterface
    {
        if (isset($this->rulesStatic[$update->requestData])) {
            return $this->container->get($this->rulesStatic[$update->requestData]); // TODO validate action
        }

        foreach ($this->routes as $route) {
            if ($route['rule']($update)) {
                return $this->container->get($route['action']); // TODO validate action
            }
        }

        throw new NotFoundException($update);
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
