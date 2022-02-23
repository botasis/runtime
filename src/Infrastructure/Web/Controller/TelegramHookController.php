<?php

namespace Viktorprogger\TelegramBot\Infrastructure\Web\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viktorprogger\TelegramBot\Domain\UpdateRuntime\Application;
use Viktorprogger\TelegramBot\Domain\UpdateRuntime\TelegramRequestFactory;
use Yiisoft\DataResponse\DataResponseFactoryInterface;

final class TelegramHookController
{
    public function __construct(
        private readonly DataResponseFactoryInterface $responseFactory,
        private readonly Application $application,
        private readonly TelegramRequestFactory $requestFactory,
    )
    {
    }

    public function hook(ServerRequestInterface $request): ResponseInterface
    {
        /** @psalm-suppress PossiblyInvalidArgument */
        $telegramRequest = $this->requestFactory->create($request->getParsedBody());
        $this->application->handle($telegramRequest);

        return $this->responseFactory->createResponse();
    }
}
