<?php

declare(strict_types=1);

namespace Botasis\Runtime\Entity\User;

/**
 * @see https://core.telegram.org/bots/api#user
 */
final readonly class User
{
    public function __construct(
        public string $id,
        public bool $isBot,
        public ?string $fistName,
        public ?string $lastName,
        public ?string $username,
        public ?string $languageCode,
        public ?bool $isPremium,
        public ?bool $addedToAttachmentMenu,
        public ?bool $canJoinGroups,
        public ?bool $canReadAllGroupMessages,
        public ?bool $supportsInlineQueries,
    ) {
    }
}
