<?php

namespace Mush\Modifier\ConfigData;

use Mush\Modifier\Enum\ModifierRequirementEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;

class ModifierActivationRequirementData
{
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
            'activationRequirementName' => ModifierRequirementEnum::STATUS,
            'activationRequirement' => EquipmentStatusEnum::HEAVY,
            'value' => 100,
        ],
        [
            'name' => 'player_status_dirty',
            'activationRequirementName' => ModifierRequirementEnum::STATUS,
            'activationRequirement' => PlayerStatusEnum::DIRTY,
            'value' => 100,
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
    ];
}
