<?php

namespace Mush\RoomLog\Enum;

use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerEvent;
use Mush\Status\Enum\PlayerStatusEnum;

class PlayerModifierLogEnum
{
    public const GAIN_TRIUMPH = 'gain_triumph';
    public const LOSS_TRIUMPH = 'loss_triumph';
    public const GAIN_ACTION_POINT = 'gain_action_point';
    public const LOSS_ACTION_POINT = 'loss_action_point';
    public const GAIN_MOVEMENT_POINT = 'gain_movement_point';
    public const LOSS_MOVEMENT_POINT = 'loss_movement_point';
    public const GAIN_HEALTH_POINT = 'gain_health_point';
    public const LOSS_HEALTH_POINT = 'loss_health_point';
    public const GAIN_MORAL_POINT = 'gain_moral_point';
    public const LOSS_MORAL_POINT = 'loss_moral_point';

    public const ANTISOCIAL_MORALE_LOSS = 'antisocial_morale_loss';
    public const PANIC_CRISIS = 'panic_crisis';

    public const GAIN = 'gain';
    public const LOSS = 'loss';

    public const PLAYER_VARIABLE_LOGS = [
        self::GAIN => [
            PlayerVariableEnum::HEALTH_POINT => self::GAIN_HEALTH_POINT,
            PlayerVariableEnum::MORAL_POINT => self::GAIN_MORAL_POINT,
            PlayerVariableEnum::MOVEMENT_POINT => self::GAIN_MOVEMENT_POINT,
            PlayerVariableEnum::ACTION_POINT => self::GAIN_ACTION_POINT,
            PlayerVariableEnum::TRIUMPH => self::GAIN_TRIUMPH,
        ],
        self::LOSS => [
            PlayerVariableEnum::HEALTH_POINT => self::LOSS_HEALTH_POINT,
            PlayerVariableEnum::MORAL_POINT => self::LOSS_MORAL_POINT,
            PlayerVariableEnum::MOVEMENT_POINT => self::LOSS_MOVEMENT_POINT,
            PlayerVariableEnum::ACTION_POINT => self::LOSS_ACTION_POINT,
            PlayerVariableEnum::TRIUMPH => self::LOSS_TRIUMPH,
        ],
    ];

    public const PLAYER_VARIABLE_SPECIAL_LOGS = [
        PlayerStatusEnum::ANTISOCIAL => self::ANTISOCIAL_MORALE_LOSS,
        PlayerEvent::PANIC_CRISIS => self::PANIC_CRISIS,
        ];
}
