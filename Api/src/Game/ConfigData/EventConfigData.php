<?php

namespace Mush\Game\ConfigData;

use Mush\Game\Event\VariableEventInterface;
use Mush\Modifier\Enum\ModifierHolderClassEnum;
use Mush\Player\Enum\PlayerVariableEnum;

/** @codeCoverageIgnore */
class EventConfigData
{
    public static $dataArray = [
        [
            'type' => 'variable_event_config',
            'quantity' => -1,
            'targetVariable' => PlayerVariableEnum::HEALTH_POINT,
            'variableHolderClass' => ModifierHolderClassEnum::PLAYER,
            'eventName' => VariableEventInterface::CHANGE_VALUE_MAX,
            'name' => 'change.value.max_player_-1_healthPoint',
        ],
        [
            'type' => 'variable_event_config',
            'quantity' => -2,
            'targetVariable' => PlayerVariableEnum::HEALTH_POINT,
            'variableHolderClass' => ModifierHolderClassEnum::PLAYER,
            'eventName' => VariableEventInterface::CHANGE_VALUE_MAX,
            'name' => 'change.value.max_player_-2_healthPoint',
        ],
        [
            'type' => 'variable_event_config',
            'quantity' => -4,
            'targetVariable' => PlayerVariableEnum::HEALTH_POINT,
            'variableHolderClass' => ModifierHolderClassEnum::PLAYER,
            'eventName' => VariableEventInterface::CHANGE_VALUE_MAX,
            'name' => 'change.value.max_player_-4_healthPoint',
        ],
        [
            'type' => 'variable_event_config',
            'quantity' => -1,
            'targetVariable' => PlayerVariableEnum::MORAL_POINT,
            'variableHolderClass' => ModifierHolderClassEnum::PLAYER,
            'eventName' => VariableEventInterface::CHANGE_VALUE_MAX,
            'name' => 'change.value.max_player_-1_moralPoint',
        ],
        [
            'type' => 'variable_event_config',
            'quantity' => -2,
            'targetVariable' => PlayerVariableEnum::MORAL_POINT,
            'variableHolderClass' => ModifierHolderClassEnum::PLAYER,
            'eventName' => VariableEventInterface::CHANGE_VALUE_MAX,
            'name' => 'change.value.max_player_-2_moralPoint',
        ],
        [
            'type' => 'variable_event_config',
            'quantity' => -3,
            'targetVariable' => PlayerVariableEnum::MORAL_POINT,
            'variableHolderClass' => ModifierHolderClassEnum::PLAYER,
            'eventName' => VariableEventInterface::CHANGE_VALUE_MAX,
            'name' => 'change.value.max_player_-3_moralPoint',
        ],
        [
            'type' => 'variable_event_config',
            'quantity' => -4,
            'targetVariable' => PlayerVariableEnum::MORAL_POINT,
            'variableHolderClass' => ModifierHolderClassEnum::PLAYER,
            'eventName' => VariableEventInterface::CHANGE_VALUE_MAX,
            'name' => 'change.value.max_player_-4_moralPoint',
        ],
        [
            'type' => 'variable_event_config',
            'quantity' => -2,
            'targetVariable' => PlayerVariableEnum::ACTION_POINT,
            'variableHolderClass' => ModifierHolderClassEnum::PLAYER,
            'eventName' => VariableEventInterface::CHANGE_VALUE_MAX,
            'name' => 'change.value.max_player_-2_actionPoint',
        ],
        [
            'type' => 'variable_event_config',
            'quantity' => -3,
            'targetVariable' => PlayerVariableEnum::MOVEMENT_POINT,
            'variableHolderClass' => ModifierHolderClassEnum::PLAYER,
            'eventName' => VariableEventInterface::CHANGE_VALUE_MAX,
            'name' => 'change.value.max_player_-3_movementPoint',
        ],
        [
            'type' => 'variable_event_config',
            'quantity' => -5,
            'targetVariable' => PlayerVariableEnum::MOVEMENT_POINT,
            'variableHolderClass' => ModifierHolderClassEnum::PLAYER,
            'eventName' => VariableEventInterface::CHANGE_VALUE_MAX,
            'name' => 'change.value.max_player_-5_movementPoint',
        ],
        [
            'type' => 'variable_event_config',
            'quantity' => -12,
            'targetVariable' => PlayerVariableEnum::MOVEMENT_POINT,
            'variableHolderClass' => ModifierHolderClassEnum::PLAYER,
            'eventName' => VariableEventInterface::CHANGE_VALUE_MAX,
            'name' => 'change.value.max_player_-12_movementPoint',
        ],
    ];
}
