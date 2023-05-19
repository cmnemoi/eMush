<?php

namespace Mush\Disease\ConfigData;

/** @codeCoverageIgnore */
class SymptomActivationRequirementData
{
    public static array $dataArray = [
      [
        'name' => 'item_in_room_schrodinger',
        'activationRequirementName' => 'item_in_room',
        'activationRequirement' => 'schrodinger',
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
        'name' => 'action_dirty_rate',
        'activationRequirementName' => 'action_dirty_rate',
        'activationRequirement' => null,
        'value' => 100,
      ],
    ];
}
