<?php

declare(strict_types=1);

namespace Botasis\Runtime\Request;

use Botasis\Client\Telegram\Request\TelegramRequestInterface;

final readonly class TelegramRequestDecorator
{
    public array $responseTags;

    public function __construct(
        public TelegramRequestInterface $request,
        string ...$responseTags
    ) {
        $this->responseTags = $responseTags;
    }
}
