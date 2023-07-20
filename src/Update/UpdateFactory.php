<?php

namespace Botasis\Runtime\Update;

use Botasis\Runtime\Entity\User\User;
use Botasis\Runtime\Entity\User\UserFactory;

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
        $message = $update['message'] ?? $update['callback_query'] ?? [];
        if ($message !== []) {
            $data = $message['text'] ?? $message['data'] ?? null;
            $messageId = (string)($message['message_id'] ?? $message['message']['message_id'] ?? '');
        } else {
            $data = $messageId = null;
        }

        $user = $this->getUser($update);

        return new Update(
            new UpdateId($update['update_id']),
            $this->getChat($update),
            $messageId,
            $data,
            $user,
            $update,
            $update['callback_query']['id'] ?? null,
        );
    }

    private function getChat(array $update): ?Chat
    {
        $data = $this->getChatData($update);
        if ($data === null) {
            return null;
        }

        return new Chat(
            $this->getChatId($data),
            ChatType::tryFrom($data['type']) ?? ChatType::UNKNOWN,
            $data['title'] ?? null,
            $this->getChatUsername($data),
            $data['first_name'] ?? null,
            $data['last_name'] ?? null,
            $data['is_forum'] ?? null,
            $this->getChatPhoto($data),
            $data['active_usernames'] ?? [],
            $data['emoji_status_custom_emoji_id'] ?? null,
            $data['bio'] ?? null,
            $data['has_private_forwards'] ?? null,
            $data['has_restricted_voice_and_video_messages'] ?? null,
            $data['join_to_send_messages'] ?? null,
            $data['join_by_request'] ?? null,
            $data['description'] ?? null,
            $data['invite_link'] ?? null,
            $data['pinned_message'] ?? [],
            $data['permissions'] ?? [],
            $data['slow_mode_delay'] ?? null,
            $data['message_auto_delete_time'] ?? null,
            $data['has_aggressive_anti_spam_enabled'] ?? null,
            $data['has_hidden_members'] ?? null,
            $data['has_protected_content'] ?? null,
            $data['sticker_set_name'] ?? null,
            $data['can_set_sticker_set'] ?? null,
            $data['linked_chat_id'] ?? null,
            $data['location'] ?? [],
            $data,
        );
    }

    private function getChatData(array $update): ?array
    {
        return $update['message']['chat']
            ?? $update['edited_message']['chat']
            ?? $update['channel_post']['chat']
            ?? $update['edited_channel_post']['chat']
            ?? $update['callback_query']['message']['chat']
            ?? $update['my_chat_member']['chat']
            ?? $update['chat_member']['chat']
            ?? $update['chat_join_request']['chat']
            ?? null;
    }

    private function getChatId(?array $chat): ?string
    {
        $id = $chat['id'] ?? null;

        if ($id === null) {
            return null;
        }

        if (str_starts_with($id, '-')) {
            $id = substr($id, 1);
        }

        return $id;
    }

    private function getChatUsername(?array $chat): ?string
    {
        $id = $chat['username'] ?? null;

        if ($id === null) {
            return null;
        }

        if (!str_starts_with($id, '@')) {
            $id = "@$id";
        }

        return $id;
    }

    private function getChatPhoto(array $data): ?ChatPhoto
    {
        if (!isset($data['photo'])) {
            return null;
        }

        return new ChatPhoto(
            $data['photo']['small_file_id'],
            $data['photo']['small_file_unique_id'],
            $data['photo']['big_file_id'],
            $data['photo']['big_file_unique_id'],
        );
    }

    /**
     * @param array $update
     * @return User|null
     */
    private function getUser(array $update): ?User
    {
        $definition = $update['message']['from']
            ?? $update['callback_query']['from']
            ?? $update['edited_message']['from']
            ?? $update['channel_post']['from']
            ?? $update['edited_channel_post']['from']
            ?? $update['inline_query']['from']
            ?? $update['chosen_inline_result']['from']
            ?? $update['shipping_query']['from']
            ?? $update['pre_checkout_query']['from']
            ?? $update['poll_answer']['user']
            ?? $update['my_chat_member']['from']
            ?? $update['chat_member']['from']
            ?? $update['chat_join_request']['from']
            ?? null;

        if (is_array($definition)) {
            return $this->userFactory->create($definition);
        }

        return null;
    }
}
