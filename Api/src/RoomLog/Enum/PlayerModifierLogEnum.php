<?php

namespace Mush\RoomLog\Enum;

use Mush\Disease\Enum\SymptomEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Modifier\Enum\ModifierNameEnum;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerEvent;
use Mush\Player\Service\PlayerService;

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

    public const SHOWER_MUSH = 'shower_mush';
    public const ANTISOCIAL_MORALE_LOSS = 'antisocial_morale_loss';
    public const PANIC_CRISIS = 'panic_crisis';
    public const CLUMSINESS = 'clumsiness';
    public const HUNGER = 'hunger';

    public const SCREAMING = 'screaming';
    public const WALL_HEAD_BANG = 'wall_head_bang';
    public const RUN_IN_CIRCLES = 'run_in_circles';
    public const FITFUL_SLEEP = 'fitful_sleep';
    public const LYING_DOWN = 'lying_down';
    public const DAILY_MORALE_LOSS = 'daily_morale_loss';

    public const GAIN = 'gain';
    public const LOSS = 'loss';

    public const VISIBILITY = 'visibility';
    public const VALUE = 'value';

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
        self::VALUE => [
            ModifierNameEnum::ANTISOCIAL_MODIFIER => self::ANTISOCIAL_MORALE_LOSS,
            ModifierNameEnum::STARVING => self::HUNGER,
            ModifierNameEnum::SCREAMING => self::SCREAMING,
            ModifierNameEnum::WALL_HEAD_BANG => self::WALL_HEAD_BANG,
            ModifierNameEnum::RUN_IN_CIRCLES => self::RUN_IN_CIRCLES,
            ModifierNameEnum::LYING_DOWN_MODIFIER => self::LYING_DOWN,
            ModifierNameEnum::FITFUL_SLEEP => self::FITFUL_SLEEP,
            SymptomEnum::BITING => SymptomEnum::BITING,
            PlayerEvent::PANIC_CRISIS => self::PANIC_CRISIS,
            EndCauseEnum::CLUMSINESS => self::CLUMSINESS,
            PlayerService::BASE_PLAYER_DAY_CHANGE => self::DAILY_MORALE_LOSS,
        ],
        self::VISIBILITY => [
            ModifierNameEnum::ANTISOCIAL_MODIFIER => VisibilityEnum::PRIVATE,
            ModifierNameEnum::STARVING => VisibilityEnum::PRIVATE,
            ModifierNameEnum::SCREAMING => VisibilityEnum::PUBLIC,
            ModifierNameEnum::WALL_HEAD_BANG => VisibilityEnum::PUBLIC,
            ModifierNameEnum::RUN_IN_CIRCLES => VisibilityEnum::PUBLIC,
            ModifierNameEnum::LYING_DOWN_MODIFIER => VisibilityEnum::HIDDEN,
            ModifierNameEnum::FITFUL_SLEEP => VisibilityEnum::PRIVATE,
            SymptomEnum::BITING => VisibilityEnum::PUBLIC,
            PlayerEvent::PANIC_CRISIS => VisibilityEnum::PRIVATE,
            EndCauseEnum::CLUMSINESS => VisibilityEnum::PRIVATE,
            PlayerService::BASE_PLAYER_DAY_CHANGE => VisibilityEnum::PRIVATE,
        ],
    ];
}
