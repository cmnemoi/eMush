<?php

declare(strict_types=1);

namespace Mush\Daedalus\Enum;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;

enum CycleIncidentEnum: string
{
    case FIRE = 'fire';
    case OXYGEN_LEAK = 'oxygen_leak';
    case ELECTROCUTION = 'electrocution';
    case ACCIDENT = 'accident';
    case FUEL_LEAK = 'fuel_leak';
    case JOLT = 'jolt';
    case EQUIPMENT_FAILURE = 'equipment_failure';
    case DOOR_BLOCKED = 'door_blocked';
    case ANXIETY_ATTACK = 'anxiety_attack';
    case BOARD_DISEASE = 'board_disease';

    public function getWeight(): int
    {
        return match ($this) {
            self::FIRE => 6,
            self::OXYGEN_LEAK => 3,
            self::ELECTROCUTION => 3,
            self::ACCIDENT => 3,
            self::FUEL_LEAK => 3,
            self::JOLT => 10,
            self::EQUIPMENT_FAILURE => 3,
            self::DOOR_BLOCKED => 10,
            self::ANXIETY_ATTACK => 5,
            self::BOARD_DISEASE => 3,
        };
    }

    public function getCost(): int
    {
        return match ($this) {
            self::FIRE => 4,
            self::OXYGEN_LEAK => 3,
            self::ELECTROCUTION => 8,
            self::ACCIDENT => 2,
            self::FUEL_LEAK => 3,
            self::JOLT => 2,
            self::EQUIPMENT_FAILURE => 3,
            self::DOOR_BLOCKED => 3,
            self::ANXIETY_ATTACK => 2,
            self::BOARD_DISEASE => 3,
        };
    }

    public function getTarget(): string
    {
        return match ($this) {
            self::FIRE, self::ELECTROCUTION, self::JOLT => Place::class,
            self::DOOR_BLOCKED, self::OXYGEN_LEAK, self::FUEL_LEAK => GameEquipment::class,
            self::ACCIDENT, self::ANXIETY_ATTACK, self::BOARD_DISEASE => Player::class,
            self::EQUIPMENT_FAILURE => 'equipment_failure',
            default => throw new \LogicException("Incident {$this->value} not supported"),
        };
    }
}
