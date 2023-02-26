<?php

namespace Mush\Disease\ConfigData;

use Mush\Status\Enum\PlayerStatusEnum;

/** @codeCoverageIgnore */
class SymptomActivationRequirementData
{
    public static array $dataArray = [
        [
            'name' => 'reason_dirty',
            'activationRequirementName' => 'reason',
            'activationRequirement' => PlayerStatusEnum::DIRTY,
            'value' => 100,
        ],
      [
        'name' => 'item_in_room_schrodinger',
        'activationRequirementName' => 'item_in_room',
        'activationRequirement' => 'schrodinger',
        'value' => 100,
      ],
      [
        'name' => 'reason_consume',
        'activationRequirementName' => 'reason',
        'activationRequirement' => 'consume',
        'value' => 100,
      ],
      [
        'name' => 'reason_consume_drug',
        'activationRequirementName' => 'reason',
        'activationRequirement' => 'consume_drug',
        'value' => 100,
      ],
      [
        'name' => 'player_equipment_schrodinger',
        'activationRequirementName' => 'player_equipment',
        'activationRequirement' => 'schrodinger',
        'value' => 100,
      ],
      [
        'name' => 'reason_move',
        'activationRequirementName' => 'reason',
        'activationRequirement' => 'move',
        'value' => 100,
      ],
      [
        'name' => 'player_in_room_mush_in_room',
        'activationRequirementName' => 'player_in_room',
        'activationRequirement' => 'mush_in_room',
        'value' => 100,
      ],
      [
        'name' => 'player_in_room_not_alone',
        'activationRequirementName' => 'player_in_room',
        'activationRequirement' => 'not_alone',
        'value' => 100,
      ],
      [
        'name' => 'random_16',
        'activationRequirementName' => 'random',
        'activationRequirement' => null,
        'value' => 16,
      ],
      [
        'name' => 'random_40',
        'activationRequirementName' => 'random',
        'activationRequirement' => null,
        'value' => 40,
      ],
      [
        'name' => 'reason_take',
        'activationRequirementName' => 'reason',
        'activationRequirement' => 'take',
        'value' => 100,
      ],
      [
        'name' => 'action_dirty_rate',
        'activationRequirementName' => 'action_dirty_rate',
        'activationRequirement' => null,
        'value' => 100,
      ],
      [
        'name' => 'player_status_dirty',
        'activationRequirementName' => 'player_status',
        'activationRequirement' => 'dirty',
        'value' => 100,
      ],
      [
        'name' => 'item_status_heavy',
        'activationRequirementName' => 'item_status',
        'activationRequirement' => 'heavy',
        'value' => 100,
      ],
      [
        'name' => 'random_50',
        'activationRequirementName' => 'random',
        'activationRequirement' => null,
        'value' => 50,
      ],
    ];
}
