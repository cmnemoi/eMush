<?php

namespace Mush\Game\ConfigData;

use Mush\Game\Event\VariableEventInterface;
use Mush\Modifier\Enum\ModifierHolderClassEnum;
use Mush\Player\Enum\PlayerVariableEnum;

/** @codeCoverageIgnore */
class VariableEventConfigData
{
    public static $dataArray = [
        [
            'quantity' => -1,
            'targetVariable' => PlayerVariableEnum::HEALTH_POINT,
            'variableHolderClass' => ModifierHolderClassEnum::PLAYER,
            'eventName' => VariableEventInterface::CHANGE_VALUE_MAX,
            'name' => 'change.value.max_player_-1_healthPoint'
        ],
        [
            'quantity' => -2,
            'targetVariable' => PlayerVariableEnum::HEALTH_POINT,
            'variableHolderClass' => ModifierHolderClassEnum::PLAYER,
            'eventName' => VariableEventInterface::CHANGE_VALUE_MAX,
            'name' => 'change.value.max_player_-2_healthPoint'
        ],
        [
            'quantity' => -4,
            'targetVariable' => PlayerVariableEnum::HEALTH_POINT,
            'variableHolderClass' => ModifierHolderClassEnum::PLAYER,
            'eventName' => VariableEventInterface::CHANGE_VALUE_MAX,
            'name' => 'change.value.max_player_-4_healthPoint'
        ],
        [
            'quantity' => -1,
            'targetVariable' => PlayerVariableEnum::MORAL_POINT,
            'variableHolderClass' => ModifierHolderClassEnum::PLAYER,
            'eventName' => VariableEventInterface::CHANGE_VALUE_MAX,
            'name' => 'change.value.max_player_-1_moralPoint'
        ],
        [
            'quantity' => -2,
            'targetVariable' => PlayerVariableEnum::MORAL_POINT,
            'variableHolderClass' => ModifierHolderClassEnum::PLAYER,
            'eventName' => VariableEventInterface::CHANGE_VALUE_MAX,
            'name' => 'change.value.max_player_-2_moralPoint'
        ],
        [
            'quantity' => -3,
            'targetVariable' => PlayerVariableEnum::MORAL_POINT,
            'variableHolderClass' => ModifierHolderClassEnum::PLAYER,
            'eventName' => VariableEventInterface::CHANGE_VALUE_MAX,
            'name' => 'change.value.max_player_-3_moralPoint'
        ],
        [
            'quantity' => -4,
            'targetVariable' => PlayerVariableEnum::MORAL_POINT,
            'variableHolderClass' => ModifierHolderClassEnum::PLAYER,
            'eventName' => VariableEventInterface::CHANGE_VALUE_MAX,
            'name' => 'change.value.max_player_-4_moralPoint'
        ],
        [
            'quantity' => -2,
            'targetVariable' => PlayerVariableEnum::ACTION_POINT,
            'variableHolderClass' => ModifierHolderClassEnum::PLAYER,
            'eventName' => VariableEventInterface::CHANGE_VALUE_MAX,
            'name' => 'change.value.max_player_-2_actionPoint'
        ],
        [
            'quantity' => -3,
            'targetVariable' => PlayerVariableEnum::MOVEMENT_POINT,
            'variableHolderClass' => ModifierHolderClassEnum::PLAYER,
            'eventName' => VariableEventInterface::CHANGE_VALUE_MAX,
            'name' => 'change.value.max_player_-3_movementPoint'
        ],
        [
            'quantity' => -5,
            'targetVariable' => PlayerVariableEnum::MOVEMENT_POINT,
            'variableHolderClass' => ModifierHolderClassEnum::PLAYER,
            'eventName' => VariableEventInterface::CHANGE_VALUE_MAX,
            'name' => 'change.value.max_player_-5_movementPoint'
        ],
        [
            'quantity' => -12,
            'targetVariable' => PlayerVariableEnum::MOVEMENT_POINT,
            'variableHolderClass' => ModifierHolderClassEnum::PLAYER,
            'eventName' => VariableEventInterface::CHANGE_VALUE_MAX,
            'name' => 'change.value.max_player_-12_movementPoint'
        ],
    ];
}
