<?php

declare(strict_types=1);

namespace Mush\Triumph\Enum;

enum TriumphTarget: string
{
    case ACTIVE_EXPLORERS = 'active_explorers';
    case AUTHOR = 'author';
    case COMMANDER = 'commander';
    case EVENT_SUBJECT = 'event_subject';
    case NONE = '';

    public function toString(): string
    {
        return $this->value;
    }
}
