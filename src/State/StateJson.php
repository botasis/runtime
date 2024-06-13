<?php

declare(strict_types=1);

namespace Botasis\Runtime\State;

use JsonException;
use Stringable;

/**
 * A most simple state implementation with json_encode()/json_decode()
 * If your state data is not json serializable, you have to implement {@see StateInterface} on your own
 *
 * @template T
 */
final readonly class StateJson implements StateInterface
{
    /**
     * @var T $data
     */
    private mixed $data;

    /**
     * @param string|null $userId
     * @param string|null $chatId
     * @param T $data
     */
    public function __construct(
        private ?string $userId,
        private ?string $chatId,
        mixed $data,
    ) {
        if (is_string($data)) {
            try {
                $this->data = json_decode($data, true, flags: JSON_THROW_ON_ERROR);
            } catch (JsonException) {
                $this->data = $data;
            }
        }
    }

    public function getUserId(): ?string
    {
        return $this->userId;
    }

    public function getChatId(): ?string
    {
        return $this->chatId;
    }

    public function getData(): string|Stringable|null
    {
        return json_encode($this->data, JSON_THROW_ON_ERROR);
    }

    /**
     * @return T
     */
    public function getDataOriginal(): mixed
    {
        return $this->data;
    }
}
