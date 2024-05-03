<?php

namespace Mush\Player\Enum;

abstract class PlayerVariableEnum
{
    public const string ACTION_POINT = 'actionPoint';
    public const string MOVEMENT_POINT = 'movementPoint';
    public const string HEALTH_POINT = 'healthPoint';
    public const string MORAL_POINT = 'moralPoint';
    public const string SATIETY = 'satiety';
    public const string TRIUMPH = 'triumph';
    public const string SPORE = 'spore';

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
