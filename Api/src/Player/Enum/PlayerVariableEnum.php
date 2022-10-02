<?php

namespace Mush\Player\Enum;

class PlayerVariableEnum
{
    public const ACTION_POINT = 'action_point';
    public const MOVEMENT_POINT = 'movement_point';
    public const HEALTH_POINT = 'health_point';
    public const MORAL_POINT = 'moral_point';
    public const SATIETY = 'satiety';
    public const TRIUMPH = 'triumph';

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
            self::SATIETY => ':hungry:',
        ];
    }
}
