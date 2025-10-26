<?php

declare(strict_types=1);

namespace Mush\Achievement\Enum;

enum StatisticEnum: string
{
    case CAT_CUDDLED = 'cat_cuddled';
    case COFFEE_TAKEN = 'coffee_taken';
    case DOOR_REPAIRED = 'door_repaired';
    case EXPLO_FEED = 'explo_feed';
    case EXTINGUISH_FIRE = 'extinguish_fire';
    case GAGGED = 'gagged';
    case GIVE_MISSION = 'give_mission';
    case NEW_PLANTS = 'new_plants';
    case PLANET_SCANNED = 'planet_scanned';
    case SIGNAL_EQUIP = 'signal_equip';
    case SIGNAL_FIRE = 'signal_fire';
    case SUCCEEDED_INSPECTION = 'succeeded_inspection';
    case NULL = '';
}
