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

    /**
     * Either inserts or updates the state with the given user and chat ids
     *
     * @param StateInterface $state
     * @return void
     */
    public function save(StateInterface $state): void;

    /**
     * Removes state data for the given user and chat ids
     *
     * @param StateInterface $state
     * @return void
     */
    public function remove(StateInterface $state): void;
}
