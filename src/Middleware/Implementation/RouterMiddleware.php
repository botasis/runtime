<?php

declare(strict_types=1);

namespace Botasis\Runtime\Middleware\Implementation;

use Botasis\Runtime\Middleware\MiddlewareInterface;
use Botasis\Runtime\Response\ResponseInterface;
use Botasis\Runtime\Router\NotFoundException;
use Botasis\Runtime\Router\Router;
use Botasis\Runtime\Update\Update;
use Botasis\Runtime\UpdateHandlerInterface;

final readonly class RouterMiddleware implements MiddlewareInterface
{
    public function __construct(private Router $router)
    {
    }

    public function process(Update $update, UpdateHandlerInterface $handler): ResponseInterface
    {
        try {
            return $this->router->match($update)->handle($update);
        } catch (NotFoundException) {
            return $handler->handle($update);
        }
    }
}
