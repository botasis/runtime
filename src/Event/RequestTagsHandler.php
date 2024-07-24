<?php

declare(strict_types=1);

namespace Botasis\Runtime\Event;

use Botasis\Runtime\CallableFactory;
use Botasis\Runtime\Request\TelegramRequestDecorator;
use Yiisoft\Injector\Injector;

final class RequestTagsHandler
{
    /**
     * @var array<string, callable[]>
     */
    private array $tagsSuccess;

    /**
     * @var array<string, callable[]>
     */
    private array $tagsError;

    /**
     * @param array<string, list<mixed>> $tagsSuccess
     * @param array<string, list<mixed>> $tagsError
     */
    public function __construct(
        CallableFactory $factory,
        private Injector $injector,
        array $tagsSuccess = [],
        array $tagsError = [],
    ) {
        foreach ($tagsSuccess as $tag => $definitions) {
            foreach ($definitions as $index => $definition) {
                $tagsSuccess[$tag][$index] = $factory->create($definition);
            }
        }
        foreach ($tagsError as $tag => $definitions) {
            foreach ($definitions as $index => $definition) {
                $tagsError[$tag][$index] = $factory->create($definition);
            }
        }

        $this->tagsSuccess = $tagsSuccess;
        $this->tagsError = $tagsError;
    }

    public function handleSuccess(TelegramRequestDecorator $request): void
    {
        array_map(
            fn(string $tag) => array_map(
                fn(callable $callable): mixed => $this->injector->invoke($callable, [$request]),
                $this->tagsSuccess[$tag],
            ),
            $request->responseTags,
        );
    }

    public function handleError(TelegramRequestDecorator $request): void
    {
        array_map(
            fn(string $tag) => array_map(
                fn(callable $callable): mixed => $this->injector->invoke($callable, [$request]),
                $this->tagsError[$tag],
            ),
            $request->responseTags,
        );
    }
}
