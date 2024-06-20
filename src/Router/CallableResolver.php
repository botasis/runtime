<?php

declare(strict_types=1);

namespace Botasis\Runtime\Router;

use Botasis\Runtime\CallableFactory;
use Botasis\Runtime\Update\Update;
use Closure;
use InvalidArgumentException;
use ReflectionFunction;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionType;
use ReflectionUnionType;
use RuntimeException;
use Yiisoft\Injector\Injector;

/**
 * Resolves a callable definition in a Closure with dependencies injected
 *
 * @internal This class is not meant to be used outside the botasis/runtime package. It won't follow semantic versioning since it's not part of public API.
 */
final readonly class CallableResolver
{
    public function __construct(private CallableFactory $callableFactory, private Injector $injector)
    {
    }

    /**
     * Resolves callable definition into a Closure with injected dependencies
     *
     * @return Closure(Update):mixed
     */
    public function resolve(array|callable|object|string $definition): Closure
    {
        /** @var array<callable(Update):mixed> $resolvers */
        $resolvers = [];
        $callable = $this->callableFactory->create($definition);
        if (!$callable instanceof Closure) {
            $callable = $callable(...);
        }

        $reflection = new ReflectionFunction($callable);
        foreach ($reflection->getParameters() as $index => $parameter) {
            if ($parameter->getType() instanceof ReflectionNamedType && $parameter->getType()->getName() === Update::class) {
                $resolvers[$index] = static fn(Update $update): Update => $update;
            }

            foreach ($parameter->getAttributes(UpdateAttribute::class) as $attribute) {
                $resolvers[$parameter->getName()] = fn(Update $update): mixed => $this->getAttributeValue(
                    $update,
                    $attribute->newInstance()->name,
                    $parameter
                );
            }
        }

        return function (Update $update) use ($callable, $resolvers): mixed {
            $parameters = array_map(static fn(callable $resolver) => $resolver($update), $resolvers);

            return $this->injector->invoke($callable, $parameters);
        };
    }

    private function getAttributeValue(Update $update, string $attributeName, ReflectionParameter $parameter): mixed
    {
        $value = $update->getAttribute($attributeName);
        if ($value === null) {
            if ($parameter->allowsNull()) {
                return null;
            }

            // TODO domain exception with explanation (use yiisoft friendly exceptions)
            throw new RuntimeException();
        }

        $type = $parameter->getType();
        switch (true) {
            case $type instanceof ReflectionNamedType:
                if ($this->typeCheck($value, $type)) {
                    return $value;
                }

                // TODO domain exception with explanation (use yiisoft friendly exceptions)
                throw new RuntimeException();
            case $type instanceof ReflectionUnionType:
                foreach ($type->getTypes() as $subType) {
                    if ($this->typeCheck($value, $subType)) {
                        return $value;
                    }
                }

                // TODO domain exception with explanation (use yiisoft friendly exceptions)
                throw new RuntimeException();
            case $type instanceof ReflectionIntersectionType:
                foreach ($type->getTypes() as $subType) {
                    if (!$this->typeCheck($value, $subType)) {
                        // TODO domain exception with explanation (use yiisoft friendly exceptions)
                        throw new RuntimeException();
                    }
                }

                return $value;
            default:
                throw new InvalidArgumentException('Unknown parameter type ' . $type::class);
        }
    }

    private function typeCheck(mixed $value, ReflectionType $type): bool
    {
        if (!$type instanceof ReflectionNamedType) {
            throw new InvalidArgumentException('Only ReflectionNamedType supported');
        }

        $typeName = $type->getName();

        return match($type->isBuiltin()) {
            true => $typeName === 'mixed' ? true : "is_$typeName"($value),
            false => (class_exists($typeName) || interface_exists($typeName)) && is_a($value, $typeName),
        };
    }
}
