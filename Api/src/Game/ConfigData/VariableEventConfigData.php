<?php

namespace Mush\Game\Service\ConfigData;

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
        ],
        [
            'quantity' => -2,
            'targetVariable' => PlayerVariableEnum::HEALTH_POINT,
            'variableHolderClass' => ModifierHolderClassEnum::PLAYER,
            'eventName' => VariableEventInterface::CHANGE_VALUE_MAX,
        ],
        [
            'quantity' => -4,
            'targetVariable' => PlayerVariableEnum::HEALTH_POINT,
            'variableHolderClass' => ModifierHolderClassEnum::PLAYER,
            'eventName' => VariableEventInterface::CHANGE_VALUE_MAX,
        ],
        [
            'quantity' => -1,
            'targetVariable' => PlayerVariableEnum::MORAL_POINT,
            'variableHolderClass' => ModifierHolderClassEnum::PLAYER,
            'eventName' => VariableEventInterface::CHANGE_VALUE_MAX,
        ],
        [
            'quantity' => -2,
            'targetVariable' => PlayerVariableEnum::MORAL_POINT,
            'variableHolderClass' => ModifierHolderClassEnum::PLAYER,
            'eventName' => VariableEventInterface::CHANGE_VALUE_MAX,
        ],
        [
            'quantity' => -3,
            'targetVariable' => PlayerVariableEnum::MORAL_POINT,
            'variableHolderClass' => ModifierHolderClassEnum::PLAYER,
            'eventName' => VariableEventInterface::CHANGE_VALUE_MAX,
        ],
        [
            'quantity' => -4,
            'targetVariable' => PlayerVariableEnum::MORAL_POINT,
            'variableHolderClass' => ModifierHolderClassEnum::PLAYER,
            'eventName' => VariableEventInterface::CHANGE_VALUE_MAX,
        ],
        [
            'quantity' => -2,
            'targetVariable' => PlayerVariableEnum::ACTION_POINT,
            'variableHolderClass' => ModifierHolderClassEnum::PLAYER,
            'eventName' => VariableEventInterface::CHANGE_VALUE_MAX,
        ],
        [
            'quantity' => -3,
            'targetVariable' => PlayerVariableEnum::MOVEMENT_POINT,
            'variableHolderClass' => ModifierHolderClassEnum::PLAYER,
            'eventName' => VariableEventInterface::CHANGE_VALUE_MAX,
        ],
        [
            'quantity' => -5,
            'targetVariable' => PlayerVariableEnum::MOVEMENT_POINT,
            'variableHolderClass' => ModifierHolderClassEnum::PLAYER,
            'eventName' => VariableEventInterface::CHANGE_VALUE_MAX,
        ],
        [
            'quantity' => -12,
            'targetVariable' => PlayerVariableEnum::MOVEMENT_POINT,
            'variableHolderClass' => ModifierHolderClassEnum::PLAYER,
            'eventName' => VariableEventInterface::CHANGE_VALUE_MAX,
        ],
    ];
}
