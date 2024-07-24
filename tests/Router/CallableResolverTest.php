<?php

declare(strict_types=1);

namespace Botasis\Runtime\Tests\Router;

use Botasis\Runtime\CallableFactory;
use Botasis\Runtime\Router\CallableResolver;
use Botasis\Runtime\Router\UpdateAttribute;
use Botasis\Runtime\Tests\Router\Support\Bar;
use Botasis\Runtime\Tests\Router\Support\Foo;
use Botasis\Runtime\Update\Update;
use Botasis\Runtime\Update\UpdateId;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Yiisoft\Injector\Injector;

final class CallableResolverTest extends TestCase
{
    public function testResolveUpdate(): void
    {
        $callableResolver = $this->getCallableResolver();

        $update = new Update(new UpdateId(1), null, '1', 'test', null, []);
        $callable = function(Update $updatePassed) use($update): void {
            $this->assertEquals($update, $updatePassed);
        };

        $callableResolver->resolve($callable)($update);
    }

    public function testResolveUpdateAttributeClass(): void
    {
        $callableResolver = $this->getCallableResolver();

        $foo = new Foo('test');
        $update = new Update(new UpdateId(1), null, '1', 'test', null, []);
        $update = $update->withAttribute('foo', $foo);
        $callable = function(
            Update $updatePassed,
            #[UpdateAttribute('foo')]
            Foo $fooPassed,
        ) use($update, $foo): void {
            $this->assertEquals($update, $updatePassed);
            $this->assertEquals($foo, $fooPassed);
        };

        $callableResolver->resolve($callable)($update);
    }

    public function testResolveUpdateAttributeClassExtended(): void
    {
        $callableResolver = $this->getCallableResolver();

        $bar = new Bar('test');
        $update = new Update(new UpdateId(1), null, '1', 'test', null, []);
        $update = $update->withAttribute('bar', $bar);
        $callable = function(
            Update $updatePassed,
            #[UpdateAttribute('bar')]
            Foo $fooPassed,
        ) use($update, $bar): void {
            $this->assertEquals($update, $updatePassed);
            $this->assertEquals($bar, $fooPassed);
        };

        $callableResolver->resolve($callable)($update);
    }

    public function testResolveUpdateAttributeScalar(): void
    {
        $callableResolver = $this->getCallableResolver();

        $update = new Update(new UpdateId(1), null, '1', 'test', null, []);
        $update = $update->withAttribute('foo', 123);
        $callable = function(
            Update $updatePassed,
            #[UpdateAttribute('foo')]
            int $foo,
        ) use($update): void {
            $this->assertEquals($update, $updatePassed);
            $this->assertEquals(123, $foo);
        };

        $callableResolver->resolve($callable)($update);
    }

    public function testResolveUpdateAttributeNotTyped(): void
    {
        $callableResolver = $this->getCallableResolver();

        $update = new Update(new UpdateId(1), null, '1', 'test', null, []);
        $update = $update->withAttribute('foo', 123);
        $callable = function(
            Update $updatePassed,
            #[UpdateAttribute('foo')]
            $foo,
        ) use($update): void {
            $this->assertEquals($update, $updatePassed);
            $this->assertEquals(123, $foo);
        };

        $callableResolver->resolve($callable)($update);
    }

    /**
     * @return CallableResolver
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function getCallableResolver(): CallableResolver
    {
        $container = $this->createMock(ContainerInterface::class);
        $callableFactory = new CallableFactory($container);
        $injector = new Injector($container);

        return new CallableResolver($callableFactory, $injector);
    }
}
