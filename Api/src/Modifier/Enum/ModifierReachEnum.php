<?php

namespace Mush\Modifier\Enum;

/**
 * Class enumerating the different modifier reaches.
 *
 * PLAYER: modifier is linked to a player
 * TARGET_PLAYER: modifier is linked to a player and activate when the player is targeted
 * PLACE: modifier is linked to a place
 * DAEDALUS: modifier is linked to the Daedalus
 * EQUIPMENT: modifier is linked to an equipment
 */
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
