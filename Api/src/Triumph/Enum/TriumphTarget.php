<?php

declare(strict_types=1);

namespace Mush\Triumph\Enum;

enum TriumphTarget: string
{
    case AUTHOR = 'author';
    case AUTHOR_CHAO = 'author_chao';
    case EVENT_SUBJECT = 'event_subject';
    case STATUS_HOLDER = 'status_holder';

    public function toString(): string
    {
        return $this->value;
    }
}
