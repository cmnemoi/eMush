<?php

namespace Mush\Modifier\Enum;

class ModifierReachEnum
{
    public const PLAYER = 'player';
    public const TARGET_PLAYER = 'target_player';
    public const PLACE = 'place';
    public const DAEDALUS = 'daedalus';
    public const EQUIPMENT = 'equipment';

    public static function getAllReaches(): array
    {
        return [
            self::DAEDALUS,
            self::PLACE,
            self::PLAYER,
            self::TARGET_PLAYER,
            self::EQUIPMENT,
        ];
    }
}
