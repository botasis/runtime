<?php

declare(strict_types=1);

namespace Botasis\Runtime\Entity\User;

/**
 * Factory for creating User objects from Telegram API data.
 */
final readonly class UserFactory
{
    public function __construct()
    {
    }

    /**
     * Converts array, received from Telegram API, into User object
     */
    public function create(array $userData): User
    {
        /**
         * @psalm-suppress MixedArgument
         */
        return new User(
            (string)$userData['id'],
            (bool) $userData['is_bot'],
            $userData['first_name'] ?? null,
            $userData['last_name'] ?? null,
            $userData['username'] ?? null,
            $userData['language_code'] ?? null,
            isset($userData['is_premium']) ? (bool) $userData['is_premium'] : null,
            isset($userData['added_to_attachment_menu']) ? (bool) $userData['added_to_attachment_menu'] : null,
            isset($userData['can_join_groups']) ? (bool) $userData['can_join_groups'] : null,
            isset($userData['can_read_all_group_messages']) ? (bool) $userData['can_read_all_group_messages'] : null,
            isset($userData['supports_inline_queries']) ? (bool) $userData['supports_inline_queries'] : null,
        );
    }
}
