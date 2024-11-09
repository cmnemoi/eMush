<?php

declare(strict_types=1);

namespace Mush\Daedalus\Enum;

enum NeronCrewLockEnum: string
{
    case NULL = '';
    case PILOTING = 'piloting';
    case PROJECTS = 'projects';
    case RESEARCH = 'research';

    public static function fromValue(string $value): NeronCrewLockEnum
    {
        return match ($value) {
            'piloting' => self::PILOTING,
            'projects' => self::PROJECTS,
            'research' => self::RESEARCH,
            default => self::NULL,
        };
    }

    public static function getValues(): array
    {
        return [
            self::PROJECTS,
            self::PILOTING,
            self::RESEARCH,
        ];
    }

    /** @return array<NeronCrewLockEnum> */
    public static function getAllExcept(self $value): array
    {
        $valuesAsString = array_map(static fn (NeronCrewLockEnum $value) => $value->toString(), self::getValues());
        $valuesExcept = array_diff($valuesAsString, [$value->toString()]);

        return array_map(static fn (string $value) => self::from($value), $valuesExcept);
    }

    public function toString(): string
    {
        return $this->value;
    }
}
