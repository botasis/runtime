<?php

declare(strict_types=1);

namespace Viktorprogger\TelegramBot\UpdateRuntime\Middleware\Implementation;

use Viktorprogger\TelegramBot\Request\RequestRepositoryInterface;
use Viktorprogger\TelegramBot\Request\TelegramRequest;
use Viktorprogger\TelegramBot\UpdateRuntime\Middleware\MiddlewareInterface;
use Viktorprogger\TelegramBot\UpdateRuntime\RequestHandlerInterface;
use Viktorprogger\TelegramBot\Response\ResponseInterface;

final readonly class RequestPersistingMiddleware implements MiddlewareInterface
{
    public function __construct(private RequestRepositoryInterface $repository)
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
