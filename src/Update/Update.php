<?php

declare(strict_types=1);

namespace Botasis\Runtime\Update;

use Botasis\Runtime\Entity\User\User;

final class Update
{
    private array $attributes = [];

    public function __construct(
        public readonly UpdateId $id,
        public readonly string $chatId,
        public readonly string $messageId,
        public readonly string $requestData,
        public readonly ?User $user,
        public readonly array $raw,
        public readonly ?string $callbackQueryId = null,
    ) {
    }

    public function getAttribute(string $attribute): mixed
    {
        return $this->attributes[$attribute] ?? null;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function withAttribute(string $attribute, mixed $value): self
    {
        $instance = $this->getNewRequest();
        $instance->attributes[$attribute] = $value;

        return $instance;
    }

    public function withoutAttribute(string $attribute): self
    {
        if (isset($this->attributes[$attribute])) {
            $instance = $this->getNewRequest();
            unset($instance->attributes[$attribute]);

            return $instance;
        }

        return $this;
    }

    /**
     * @return Update
     */
    private function getNewRequest(): Update
    {
        return new self(
            $this->id,
            $this->chatId,
            $this->messageId,
            $this->requestData,
            $this->user,
            $this->raw,
            $this->callbackQueryId,
        );
    }
}
