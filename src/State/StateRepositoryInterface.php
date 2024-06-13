<?php

declare(strict_types=1);

namespace Botasis\Runtime\State;

interface StateRepositoryInterface
{
    /**
     * @param string|null $userId Telegram user id
     * @param string|null $chatId Telegram chat id
     * @return StateInterface|null
     */
    public function find(?string $userId, ?string $chatId): ?StateInterface;

    public function save(StateInterface $state): void;

    public function remove(StateInterface $state): void;
}
