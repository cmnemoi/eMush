<?php

namespace Mush\Modifier\Enum;

/**
 * Class enumerating the entities that can hold a gameModifier.
 *
 * PLAYER: modifier is linked to a player
 * PLACE: modifier is linked to a place
 * DAEDALUS: modifier is linked to the Daedalus
 * EQUIPMENT: modifier is linked to an equipment
 */
abstract class ModifierHolderClassEnum
{
    public const string PLAYER = 'player';
    public const string PLACE = 'place';
    public const string DAEDALUS = 'daedalus';
    public const string EQUIPMENT = 'equipment';

    public static function getAllModifierHolderClass(): array
    {
        return [
            self::DAEDALUS,
            self::PLACE,
            self::PLAYER,
            self::EQUIPMENT,
        ];
    }
}
