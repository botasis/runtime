<?php

declare(strict_types=1);

namespace Botasis\Runtime\Router;

final class RuleDynamic
{
    /**
     * @var callable
     */
    private $callback;

    /**
     * Creates a dynamic route rule. Accepts a callable which should decide if a route should handle
     * the given update.
     * Example: new RuleDynamic(static fn(Update $update) => $update->chat->type === ChatType::PRIVATE)
     *
     * @param callable $callback It should have signature: function(Update $update): bool
     */
    public function __construct(callable $callback) {
        $this->callback = $callback;
    }

    public function getCallback(): callable
    {
        return $this->callback;
    }
}
