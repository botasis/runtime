<?php

declare(strict_types=1);

namespace Viktorprogger\TelegramBot\Infrastructure\Entity\Request\Cycle;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use DateTimeImmutable;

#[Entity(table: 'viktorprogger_telegram_request')]
class RequestEntity
{
    #[Column(type: 'integer', primary: true)]
    public int $id;

    #[Column(type: 'timestamp')]
    public DateTimeImmutable $created_at;

    #[Column(type: 'longText')]
    public string $contents;
}
