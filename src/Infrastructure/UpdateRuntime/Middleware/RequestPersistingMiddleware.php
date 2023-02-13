<?php

declare(strict_types=1);

namespace Viktorprogger\TelegramBot\Infrastructure\UpdateRuntime\Middleware;

use Viktorprogger\TelegramBot\Domain\Client\ResponseInterface;
use Viktorprogger\TelegramBot\Domain\Entity\Request\RequestRepositoryInterface;
use Viktorprogger\TelegramBot\Domain\Entity\Request\TelegramRequest;
use Viktorprogger\TelegramBot\Domain\UpdateRuntime\Middleware\MiddlewareInterface;
use Viktorprogger\TelegramBot\Domain\UpdateRuntime\RequestHandlerInterface;

final class RequestPersistingMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly RequestRepositoryInterface $repository)
    {
    }

    public function process(TelegramRequest $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        if (!$this->repository->find($request->id)) {
            $this->repository->create($request);
        }

        return $response;
    }
}
