<?php

namespace Mush\RoomLog\Enum;

use Mush\Disease\Enum\SymptomEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Hunter\Event\HunterEvent;
use Mush\Modifier\Enum\ModifierNameEnum;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerEvent;
use Mush\Player\Service\PlayerService;

abstract class PlayerModifierLogEnum
{
    public const string GAIN_TRIUMPH = 'gain_triumph';
    public const string LOSS_TRIUMPH = 'loss_triumph';
    public const string GAIN_ACTION_POINT = 'gain_action_point';
    public const string LOSS_ACTION_POINT = 'loss_action_point';
    public const string GAIN_MOVEMENT_POINT = 'gain_movement_point';
    public const string LOSS_MOVEMENT_POINT = 'loss_movement_point';
    public const string GAIN_HEALTH_POINT = 'gain_health_point';
    public const string LOSS_HEALTH_POINT = 'loss_health_point';
    public const string GAIN_MORAL_POINT = 'gain_moral_point';
    public const string LOSS_MORAL_POINT = 'loss_moral_point';
    public const string PANIC_CRISIS = 'panic_crisis';
    public const string CLUMSINESS = 'clumsiness';
    public const string HUNGER = 'hunger';
    public const string DAILY_MORALE_LOSS = 'daily_morale_loss';
    public const string LOGISTIC_LOG = 'logistic_log';
    public const string OPTIMIST_WORKED = 'optimist_worked';
    public const string COLD_BLOODED_WORKED = 'cold_blooded_worked';
    public const string OPPORTUNIST_WORKED = 'opportunist_worked';
    public const string LETHARGY_WORKED = 'lethargy_worked';
    public const string PANIC_WORKED = 'panic_worked';
    public const string SELF_SACRIFICE_WORKED = 'self_sacrifice_worked';
    public const string GAIN = 'gain';
    public const string LOSS = 'loss';
    public const string VISIBILITY = 'visibility';
    public const string VALUE = 'value';

    public const array PLAYER_VARIABLE_LOGS = [
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

    public const array PLAYER_VARIABLE_SPECIAL_LOGS = [
        self::VALUE => [
            ModifierNameEnum::STARVING => self::HUNGER,
            SymptomEnum::BITING => SymptomEnum::BITING,
            PlayerEvent::PANIC_CRISIS => self::PANIC_CRISIS,
            EndCauseEnum::CLUMSINESS => self::CLUMSINESS,
            ModifierNameEnum::OPTIMIST_MODIFIER => self::OPTIMIST_WORKED,
            PlayerService::DAY_MORAL_CHANGE => self::DAILY_MORALE_LOSS,
            HunterEvent::HUNTER_SHOT => LogEnum::ATTACKED_BY_HUNTER,
            ModifierNameEnum::LOGISTICS_MODIFIER => self::LOGISTIC_LOG,
            ModifierNameEnum::COLD_BLOODED_MODIFIER => self::COLD_BLOODED_WORKED,
            ModifierNameEnum::OPPORTUNIST_MODIFIER => self::OPPORTUNIST_WORKED,
            ModifierNameEnum::LETHARGY_MODIFIER => self::LETHARGY_WORKED,
            ModifierNameEnum::PANIC_ACTION_POINT_MODIFIER => self::PANIC_WORKED,
            ModifierNameEnum::PANIC_MOVEMENT_POINT_MODIFIER => self::PANIC_WORKED,
            ModifierNameEnum::SELF_SACRIFICE_MODIFIER => self::SELF_SACRIFICE_WORKED,
        ],
        self::VISIBILITY => [
            ModifierNameEnum::STARVING => VisibilityEnum::PRIVATE,
            SymptomEnum::BITING => VisibilityEnum::PUBLIC,
            PlayerEvent::PANIC_CRISIS => VisibilityEnum::PRIVATE,
            EndCauseEnum::CLUMSINESS => VisibilityEnum::PRIVATE,
            PlayerService::DAY_MORAL_CHANGE => VisibilityEnum::PRIVATE,
            HunterEvent::HUNTER_SHOT => VisibilityEnum::PUBLIC,
            ModifierNameEnum::LOST_MODIFIER => VisibilityEnum::PRIVATE,
            ModifierNameEnum::OPTIMIST_MODIFIER => VisibilityEnum::PRIVATE,
            ModifierNameEnum::LOGISTICS_MODIFIER => VisibilityEnum::PRIVATE,
            ModifierNameEnum::COLD_BLOODED_MODIFIER => VisibilityEnum::PRIVATE,
            ModifierNameEnum::OPPORTUNIST_MODIFIER => VisibilityEnum::PRIVATE,
            ModifierNameEnum::LETHARGY_MODIFIER => VisibilityEnum::PRIVATE,
            ModifierNameEnum::PANIC_ACTION_POINT_MODIFIER => VisibilityEnum::PRIVATE,
            ModifierNameEnum::PANIC_MOVEMENT_POINT_MODIFIER => VisibilityEnum::PRIVATE,
            ModifierNameEnum::SELF_SACRIFICE_MODIFIER => VisibilityEnum::PRIVATE,
        ],
    ];
}
