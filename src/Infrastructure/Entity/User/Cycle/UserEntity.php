<?php

declare(strict_types=1);

namespace Viktorprogger\TelegramBot\Infrastructure\Entity\User\Cycle;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;

#[Entity]
final class UserEntity
{
    public function __construct(
        #[Column(type: 'string', primary: true)]
        public string $id,
    ) {
    }
}
