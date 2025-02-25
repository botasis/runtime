<?php

declare(strict_types=1);

namespace Botasis\Runtime\Event;

use Botasis\Runtime\CallableFactory;
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
     * @param array<string, list<string|array>> $tagsSuccess
     * @param array<string, list<string|array>> $tagsError
     */
    public function __construct(
        CallableFactory $factory,
        private Injector $injector,
        array $tagsSuccess = [],
        array $tagsError = [],
    ) {
        $listenersSuccess = $listenersError = [];
        foreach ($tagsSuccess as $tag => $definitions) {
            foreach ($definitions as $index => $definition) {
                $listenersSuccess[$tag][$index] = $factory->create($definition);
            }
        }
        foreach ($tagsError as $tag => $definitions) {
            foreach ($definitions as $index => $definition) {
                $listenersError[$tag][$index] = $factory->create($definition);
            }
        }

        $this->tagsSuccess = $listenersSuccess;
        $this->tagsError = $listenersError;
    }

    public function handleSuccess(RequestSuccessEvent $event): void
    {
        array_map(
            fn(string $tag) => array_map(
                fn(callable $callable): mixed => $this->injector->invoke(
                    $callable,
                    [$event->update, $event->request, $event->responseDecoded],
                ),
                $this->tagsSuccess[$tag],
            ),
            $event->request->responseTags,
        );
    }

    public function handleError(RequestErrorEvent $event): void
    {
        array_map(
            fn(string $tag) => array_map(
                fn(callable $callable): mixed => $this->injector->invoke(
                    $callable,
                    [$event->update, $event->request, $event->exception],
                ),
                $this->tagsError[$tag],
            ),
            $event->request->responseTags,
        );
    }
}
