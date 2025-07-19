<?php

declare(strict_types=1);

namespace Mush\Daedalus\Enum;

use Mush\Status\Enum\EquipmentStatusEnum;

enum NeronFoodDestructionEnum: string
{
    case NEVER = 'never';
    case UNSTABLE = 'unstable';
    case HAZARDOUS = 'hazardous';
    case DECOMPOSING = 'decomposing';

    public static function fromValue(string $value): NeronFoodDestructionEnum
    {
        return match ($value) {
            'unstable' => self::UNSTABLE,
            'hazardous' => self::HAZARDOUS,
            'decomposing' => self::DECOMPOSING,
            default => self::NEVER,
        };
    }

    public static function getValues(): array
    {
        return [
            self::UNSTABLE,
            self::HAZARDOUS,
            self::DECOMPOSING,
            self::NEVER,
        ];
    }

    /** @return array<NeronFoodDestructionEnum> */
    public static function getAllExcept(self $value): array
    {
        $valuesAsString = array_map(static fn (NeronFoodDestructionEnum $value) => $value->toString(), self::getValues());
        $valuesExcept = array_diff($valuesAsString, [$value->toString()]);

        return array_map(static fn (string $value) => self::from($value), $valuesExcept);
    }

    public function toString(): string
    {
        return $this->value;
    }

    public static function getStatus(self $value): array
    {
        return match ($value) {
            self::NEVER => [],
            self::DECOMPOSING => [EquipmentStatusEnum::DECOMPOSING],
            self::HAZARDOUS => [EquipmentStatusEnum::DECOMPOSING, EquipmentStatusEnum::HAZARDOUS],
            self::UNSTABLE => [EquipmentStatusEnum::DECOMPOSING, EquipmentStatusEnum::HAZARDOUS, EquipmentStatusEnum::UNSTABLE],
            default => []
        };
    }
}
