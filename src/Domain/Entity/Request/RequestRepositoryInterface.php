<?php

declare(strict_types=1);

namespace Viktorprogger\TelegramBot\Domain\Entity\Request;

interface RequestRepositoryInterface
{
    /**
     * Persist to a storage
     *
     * @param TelegramRequest $request
     */
    public function create(TelegramRequest $request): void;

    /**
     * Find a persisted request entity by the given id
     *
     * @param RequestId $id
     *
     * @return TelegramRequest|null
     */
    public function find(RequestId $id): ?TelegramRequest;

    /**
     * Get the biggest persisted ID
     *
     * @return RequestId|null
     */
    public function getBiggestId(): ?RequestId;
}
