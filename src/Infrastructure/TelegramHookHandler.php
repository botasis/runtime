<?php

declare(strict_types=1);

namespace Viktorprogger\TelegramBot\Infrastructure;

use Viktorprogger\TelegramBot\Domain\Entity\Request\TelegramRequestFactory;
use Viktorprogger\TelegramBot\Domain\UpdateRuntime\Application;
use Yiisoft\Yii\Queue\Message\MessageInterface;

final class TelegramHookHandler
{
    public const NAME = 'telegram-hook';

    public function __construct(
        private readonly Application $application,
        private readonly TelegramRequestFactory $requestFactory,
    ) {
    }

    public function __invoke(MessageInterface $message): void
    {
        $telegramRequest = $this->requestFactory->create($message->getData());
        $this->application->handle($telegramRequest);
    }
}
