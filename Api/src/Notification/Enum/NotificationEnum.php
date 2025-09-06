<?php

declare(strict_types=1);

namespace Mush\Notification\Enum;

enum NotificationEnum: string
{
    case INACTIVITY = 'inactivity';

    public function toString(): string
    {
        return $this->value;
    }

    public function toTranslationTitleKey(): string
    {
        return $this->value . '.title';
    }

    public function toTranslationBodyKey(): string
    {
        return $this->value . '.description';
    }
}
