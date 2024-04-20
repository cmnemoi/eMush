<?php

namespace Mush\Modifier\Enum;

/**
 * Class enumerating the entities that can hold a gameModifier.
 *
 * PLAYER: modifier is linked to a player
 * TARGET_PLAYER: modifier is linked to a player and activate when the player is targeted
 * PLACE: modifier is linked to a place
 * DAEDALUS: modifier is linked to the Daedalus
 * EQUIPMENT: modifier is linked to an equipment
 */
abstract class ModifierHolderClassEnum
{
    public const string PLAYER = 'player';
    public const string TARGET_PLAYER = 'target_player';
    public const string PLACE = 'place';
    public const string DAEDALUS = 'daedalus';
    public const string EQUIPMENT = 'equipment';

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
