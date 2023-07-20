<?php

declare(strict_types=1);

namespace Botasis\Runtime\Update;

enum ChatType: string
{
    case PRIVATE = 'private';
    case GROUP = 'group';
    case SUPERGROUP = 'supergroup';
    case CHANNEL = 'channel';
    case UNKNOWN = 'unknown';
}
