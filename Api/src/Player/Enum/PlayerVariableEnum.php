<?php

namespace Mush\Player\Enum;

class PlayerVariableEnum
{
    public const ACTION_POINT = 'actionPoint';
    public const MOVEMENT_POINT = 'movementPoint';
    public const HEALTH_POINT = 'healthPoint';
    public const MORAL_POINT = 'moralPoint';
    public const SATIETY = 'satiety';
    public const TRIUMPH = 'triumph';

    public const SPORE = 'spore';

    public static function getCappedPlayerVariables(): array
    {
        return [
            self::ACTION_POINT,
            self::MOVEMENT_POINT,
            self::MORAL_POINT,
            self::HEALTH_POINT,
        ];
    }

    public static function getEmoteMap(): array
    {
        return [
            self::ACTION_POINT => ':pa:',
            self::MOVEMENT_POINT => ':pm:',
            self::HEALTH_POINT => ':hp:',
            self::MORAL_POINT => ':pmo:',
            self::SATIETY => ':pa_cook:',
        ];
    }
}
