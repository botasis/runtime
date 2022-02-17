<?php

namespace Viktorprogger\TelegramBot\Infrastructure\Web\Controller;

use Psr\Http\Message\ServerRequestInterface;
use Viktorprogger\TelegramBot\Domain\UpdateRuntime\Application;
use Yiisoft\DataResponse\DataResponseFactoryInterface;

class TelegramHookController
{
    public function __construct(
        private readonly DataResponseFactoryInterface $responseFactory,
        private readonly Application $application,
    )
    {
    }

    public function hook(ServerRequestInterface $request)
    {
        $this->application->handle($request->getParsedBody());

        return $this->responseFactory->createResponse();
    }
}
