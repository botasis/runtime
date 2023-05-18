<?php

namespace Botasis\Runtime\Router;

use Botasis\Runtime\Update\Update;
use Botasis\Runtime\UpdateHandlerInterface;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;

final readonly class Router
{
    public const ROUTE_KEY_RULE_STATIC = 'rule_static';
    public const ROUTE_KEY_RULE = 'rule';
    public const ROUTE_KEY_ACTION = 'action';
    public const ROUTE_KEY_ACTION_LIST = 'actions';
    private array $rulesStatic;

    /**
     * @psalm-param $routes list<array{rule: callable, action: class-string<UpdateHandlerInterface>}>
     */
    public function __construct(private ContainerInterface $container, private array $routes)
    {
        $rulesStatic = [];
        foreach ($this->routes as &$route) {
            $isGroup = ($route[self::ROUTE_KEY_ACTION_LIST] ?? []) !== [];
            if (!$isGroup xor is_string($route[self::ROUTE_KEY_ACTION])) {
                throw new InvalidArgumentException('Telegram route must have either "action" or "actions" key.');
            }

            if ($isGroup) {
                $route[self::ROUTE_KEY_ACTION] = new self($this->container, $route[self::ROUTE_KEY_ACTION_LIST]);
            }

            $staticRule = $this->getRuleStatic($route);
            $hasStaticRule = $staticRule !== [];
            if (!$hasStaticRule xor isset($route[self::ROUTE_KEY_RULE])) {
                throw new InvalidArgumentException('Telegram route must have either "rule_static" or "rule" key.');
            }

            if ($hasStaticRule) {
                $rulesStatic[$staticRule[0]] = $staticRule[1];
            }
        }
        unset($route);

        $this->rulesStatic = $rulesStatic;
    }

    public function match(Update $update): UpdateHandlerInterface
    {
        $action = null;
        if (isset($this->rulesStatic[$update->requestData])) {
            $action = $this->rulesStatic[$update->requestData];
        }

        foreach ($this->routes as $route) {
            if ($route[self::ROUTE_KEY_RULE]($update)) {
                $action = $route[self::ROUTE_KEY_ACTION];
            }
        }

        if (is_string($action)) {
            return $this->container->get($action);
        }

        if ($action instanceof self) {
            return $action->match($update);
        }

        if ($action === null) {
            throw new NotFoundException($update);
        }

        throw new InvalidArgumentException('Telegram route action must be a string identifier of an UpdateHandlerInterface class.');
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
