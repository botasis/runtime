<?php

declare(strict_types=1);

namespace Botasis\Runtime\Update;

final readonly class Chat
{
    public function __construct(
        public string $id,
        public ChatType $type,
        public ?string $title,
        public ?string $username,
        public ?string $firstName,
        public ?string $lastName,
        public ?bool $isForum,
        public ?ChatPhoto $photo,
        public array $activeUsernames,
        public ?string $emojiStatusCustomEmojiId,
        public ?string $bio,
        public ?bool $hasPrivateForwards,
        public ?bool $hasRestrictedVoiceAndVideoMessages,
        public ?bool $joinToSendMessages,
        public ?bool $joinByRequest,
        public ?string $description,
        public ?string $inviteLink,
        public ?array $pinnedMessage,
        public ?array $permissions,
        public ?int $slowModeDelay,
        public ?int $messageAutoDeleteTime,
        public ?bool $hasAggressiveAntiSpamEnabled,
        public ?bool $hasHiddenMembers,
        public ?bool $hasProtectedContent,
        public ?string $stickerSetName,
        public ?string $canSetStickerSet,
        public ?string $linkedChatId,
        public ?array $location,
        /** Raw data in form of array */
        public array $raw,
    ) {
    }
}
