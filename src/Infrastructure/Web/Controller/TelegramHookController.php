<?php

namespace Viktorprogger\TelegramBot\Infrastructure\Web\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viktorprogger\TelegramBot\Domain\Entity\Request\TelegramRequestFactory;
use Viktorprogger\TelegramBot\Domain\UpdateRuntime\Application;
use Viktorprogger\TelegramBot\Infrastructure\TelegramHookHandler;
use Yiisoft\DataResponse\DataResponseFactoryInterface;
use Yiisoft\Yii\Queue\Message\Message;
use Yiisoft\Yii\Queue\Queue;

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

    public function hookQueued(ServerRequestInterface $request, Queue $queue): ResponseInterface
    {
        $message = new Message(TelegramHookHandler::NAME, $request->getParsedBody());
        $queue->push($message);

        return $this->responseFactory->createResponse();
    }
}
