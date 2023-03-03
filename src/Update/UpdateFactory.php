<?php

namespace Viktorprogger\TelegramBot\Update;

use Viktorprogger\TelegramBot\Entity\User\UserFactory;

final readonly class UpdateFactory
{
    public function __construct(
        private UserFactory $userFactory,
    ) {
    }

    /**
     * Making a TelegramRequest object from a telegram update data
     * @see https://core.telegram.org/bots/api#update
     *
     * @param array $update An update entry
     *
     * @return Update
     */
    public function create(array $update): Update
    {
        $message = $update['message'] ?? $update['callback_query'];
        $data = trim($message['text'] ?? $message['data']);
        $chatId = (string)($message['chat']['id'] ?? $message['message']['chat']['id']);
        $messageId = (string)($message['message_id'] ?? $message['message']['message_id']);
        $user = isset($message['from']) ? $this->userFactory->create($message['from']) : null;

        return new Update(
            new UpdateId($update['update_id']),
            $chatId,
            $messageId,
            $data,
            $user,
            $update,
            $update['callback_query']['id'] ?? null,
        );
    }
}
