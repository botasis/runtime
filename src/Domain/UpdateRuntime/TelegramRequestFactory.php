<?php

namespace Viktorprogger\TelegramBot\Domain\UpdateRuntime;

use Viktorprogger\TelegramBot\Domain\TelegramUserFactory;
use Viktorprogger\TelegramBot\Domain\UpdateRuntime\TelegramRequest;

final class TelegramRequestFactory
{
    public function __construct(
        private readonly TelegramUserFactory $subscriberService,
    )
    {
    }

    /**
     * Making a TelegramRequest object from a telegram update data
     * @see https://core.telegram.org/bots/api#update
     *
     * @param array $update An update entry
     *
     * @return TelegramRequest
     */
    public function create(array $update): TelegramRequest
    {
        $message = $update['message'] ?? $update['callback_query'];
        $data = trim($message['text'] ?? $message['data']);
        $chatId = (string) ($message['chat']['id'] ?? $message['message']['chat']['id']);
        $messageId = (string) ($message['message_id'] ?? $message['message']['message_id']);
        $subscriber = $this->subscriberService->getSubscriber($message['from']['id'], $chatId);

        return new TelegramRequest(
            $chatId,
            $messageId,
            $data,
            $subscriber,
            $update['callback_query']['id'] ?? null
        );
    }
}
