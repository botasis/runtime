<?php

declare(strict_types=1);

namespace Botasis\Runtime\UpdateRuntime\Middleware\Implementation;

use Botasis\Runtime\Update\Update;
use Botasis\Runtime\Response\ResponseInterface;
use Botasis\Runtime\UpdateRuntime\Middleware\MiddlewareInterface;
use Botasis\Runtime\UpdateRuntime\NotFoundException;
use Botasis\Runtime\UpdateRuntime\RequestHandlerInterface;
use Botasis\Runtime\UpdateRuntime\Router;

final readonly class RouterMiddleware implements MiddlewareInterface
{
    public function __construct(private Router $router)
    {
    }

    public function process(Update $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $this->router->match($request)->handle($request);
        } catch (NotFoundException) {
            return $handler->handle($request);
        }
    }
}
