<?php

declare(strict_types=1);

namespace Mush\Daedalus\Enum;

enum NeronCrewLockEnum: string
{
    case NULL = '';
    case PILOTING = 'piloting';
    case PROJECTS = 'projects';

    public static function fromValue(string $value): NeronCrewLockEnum
    {
        return match ($value) {
            'piloting' => self::PILOTING,
            'projects' => self::PROJECTS,
            default => self::NULL,
        };
    }
}
