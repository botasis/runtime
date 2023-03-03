<?php

declare(strict_types=1);

namespace Botasis\Runtime\Entity\User;

final readonly class UserFactory
{
    public function __construct()
    {
    }

    public function create(array $userData): User
    {
        return new User(
            new UserId($userData['id']),
            (bool) $userData['is_bot'],
            $userData['first_name'],
            $userData['last_name'],
            $userData['username'],
            $userData['language_code'],
            isset($userData['is_premium']) ? (bool) $userData['is_premium'] : null,
            isset($userData['added_to_attachment_menu']) ? (bool) $userData['added_to_attachment_menu'] : null,
            isset($userData['can_join_groups']) ? (bool) $userData['can_join_groups'] : null,
            isset($userData['can_read_all_group_messages']) ? (bool) $userData['can_read_all_group_messages'] : null,
            isset($userData['supports_inline_queries']) ? (bool) $userData['supports_inline_queries'] : null,
        );
    }
}
