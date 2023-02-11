<?php

namespace Viktorprogger\TelegramBot\Domain\Entity\Request;

use Viktorprogger\TelegramBot\Domain\Entity\User\UserFactory;

final readonly class TelegramRequestFactory
{
    public function __construct(
        private UserFactory $userFactory,
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
        $user = $this->userFactory->create($message['from']['id']);

        return new TelegramRequest(
            new RequestId($update['update_id']),
            $chatId,
            $messageId,
            $data,
            $user,
            $update,
            $update['callback_query']['id'] ?? null,
        );
    }
}
