<?php

declare(strict_types=1);

namespace Viktorprogger\TelegramBot\Domain\UpdateRuntime;

use Viktorprogger\YiisoftInform\Domain\Entity\Subscriber\Subscriber;

final class TelegramRequest
{
    public function __construct(
        public readonly string $chatId,
        public readonly string $messageId,
        public readonly string $requestData,
        public readonly Subscriber $subscriber,
        public readonly ?string $callbackQueryId = null,
    ) {
    }
}
