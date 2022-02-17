<?php

declare(strict_types=1);

namespace Viktorprogger\TelegramBot\Infrastructure\Entity\Update\Cycle;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use DateTimeImmutable;
use Viktorprogger\TelegramBot\Infrastructure\Entity\TgUpdateEntityCycleRepository;

#[Entity(table: 'tg_update', repository: TgUpdateEntityCycleRepository::class)]
final class TelegramUpdateEntity
{
    #[Column(type: 'int', primary: true)]
    public int $id;

    #[Column(type: 'timestamp')]
    public DateTimeImmutable $created_at;

    #[Column(type: 'longText')]
    public string $contents;
}
