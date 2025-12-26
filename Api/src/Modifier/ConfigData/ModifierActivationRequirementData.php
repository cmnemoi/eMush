<?php

namespace Mush\Modifier\ConfigData;

use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Modifier\Enum\ModifierRequirementEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;

class ModifierActivationRequirementData
{
    public const string PLAYER_STATUS_DIRTY = 'player_status_dirty';

    public static array $dataArray = [
        [
            'name' => 'random_10',
            'activationRequirementName' => 'random',
            'activationRequirement' => null,
            'value' => 10,
        ],
        [
            'name' => 'random_16',
            'activationRequirementName' => 'random',
            'activationRequirement' => null,
            'value' => 16,
        ],
        [
            'name' => 'random_20',
            'activationRequirementName' => 'random',
            'activationRequirement' => null,
            'value' => 20,
        ],
        [
            'name' => 'random_25',
            'activationRequirementName' => 'random',
            'activationRequirement' => null,
            'value' => 25,
        ],
        [
            'name' => 'random_30',
            'activationRequirementName' => 'random',
            'activationRequirement' => null,
            'value' => 30,
        ],
        [
            'name' => 'random_40',
            'activationRequirementName' => 'random',
            'activationRequirement' => null,
            'value' => 40,
        ],
        [
            'name' => 'random_50',
            'activationRequirementName' => 'random',
            'activationRequirement' => null,
            'value' => 50,
        ],
        [
            'name' => 'player_status_lying_down',
            'activationRequirementName' => 'status',
            'activationRequirement' => 'lying_down',
            'value' => 100,
        ],
        [
            'name' => 'item_status_heavy',
            'activationRequirementName' => ModifierRequirementEnum::HOLDER_HAS_STATUS,
            'activationRequirement' => EquipmentStatusEnum::HEAVY,
            'value' => 100,
        ],
        [
            'name' => self::PLAYER_STATUS_DIRTY,
            'activationRequirementName' => ModifierRequirementEnum::HOLDER_HAS_STATUS,
            'activationRequirement' => PlayerStatusEnum::DIRTY,
            'value' => 100,
        ],
        [
            'name' => ModifierRequirementEnum::PLAYER_IS_NOT_MUSH,
            'activationRequirementName' => ModifierRequirementEnum::HOLDER_HAS_STATUS,
            'activationRequirement' => PlayerStatusEnum::MUSH,
            'value' => ModifierRequirementEnum::ABSENT_STATUS,
        ],
        [
            'name' => 'player_equipment_schrodinger',
            'activationRequirementName' => 'player_equipment',
            'activationRequirement' => 'schrodinger',
            'value' => 100,
        ],
        [
            'name' => 'cycle_even',
            'activationRequirementName' => 'cycle',
            'activationRequirement' => 'even',
            'value' => 100,
        ],
        [
            'name' => 'item_in_room_schrodinger',
            'activationRequirementName' => 'item_in_room',
            'activationRequirement' => 'schrodinger',
            'value' => 100,
        ],
        [
            'name' => 'player_in_room_four_people',
            'activationRequirementName' => 'player_in_room',
            'activationRequirement' => 'four_people',
            'value' => 100,
        ],
        [
            'name' => 'player_in_room_mush',
            'activationRequirementName' => 'player_in_room',
            'activationRequirement' => 'mush_in_room',
            'value' => 100,
        ],
        [
            'name' => 'random_70',
            'activationRequirementName' => 'random',
            'activationRequirement' => null,
            'value' => 70,
        ],
        [
            'name' => 'player_in_room_not_alone',
            'activationRequirementName' => 'player_in_room',
            'activationRequirement' => 'not_alone',
            'value' => 100,
        ],
        [
            'name' => 'holder_name_turret',
            'activationRequirementName' => ModifierRequirementEnum::HOLDER_NAME,
            'activationRequirement' => EquipmentEnum::TURRET_COMMAND,
            'value' => 100,
        ],
        [
            'name' => 'holder_name_patrol_ship',
            'activationRequirementName' => ModifierRequirementEnum::HOLDER_NAME,
            'activationRequirement' => EquipmentEnum::PATROL_SHIP,
            'value' => 100,
        ],
        [
            'name' => ModifierRequirementEnum::LYING_DOWN_STATUS_CHARGE_REACHES_4,
            'activationRequirementName' => ModifierRequirementEnum::STATUS_CHARGE_REACHES,
            'activationRequirement' => PlayerStatusEnum::LYING_DOWN,
            'value' => 4,
        ],
        [
            'name' => ModifierRequirementEnum::MUSH_CREW_PROPORTION_50_PERCENTS,
            'activationRequirementName' => ModifierRequirementEnum::MUSH_CREW_PROPORTION,
            'activationRequirement' => '',
            'value' => 50,
        ],
        [
            'name' => ModifierRequirementEnum::PLAYER_IS_NOT_HYPERACTIVE,
            'activationRequirementName' => ModifierRequirementEnum::HOLDER_HAS_STATUS,
            'activationRequirement' => PlayerStatusEnum::HYPERACTIVE,
            'value' => ModifierRequirementEnum::ABSENT_STATUS,
        ],
        [
            'name' => ModifierRequirementEnum::PLAYER_IS_NOT_SPLASHPROOF,
            'activationRequirementName' => ModifierRequirementEnum::HOLDER_HAS_SKILL,
            'activationRequirement' => 'splashproof',
            'value' => ModifierRequirementEnum::ABSENT_SKILL,
        ],
        [
            'name' => ModifierRequirementEnum::PLAYER_HAS_NOT_USED_FERTILE_TODAY,
            'activationRequirementName' => ModifierRequirementEnum::HOLDER_HAS_STATUS,
            'activationRequirement' => PlayerStatusEnum::HAS_USED_FERTILE_TODAY,
            'value' => ModifierRequirementEnum::ABSENT_STATUS,
        ],
        [
            'name' => ModifierRequirementEnum::PLAYER_ANY_WEAPON,
            'activationRequirementName' => ModifierRequirementEnum::PLAYER_ANY_WEAPON,
            'activationRequirement' => '',
            'value' => 100,
        ],
    ];

    public static function getByName(string $name): array
    {
        $data = current(
            array_filter(
                self::$dataArray,
                static fn ($data) => $data['name'] === $name
            )
        );
        if (!$data) {
            throw new \Exception("Modifier activation requirement {$name} not found");
        }

        return $data;
    }
}
