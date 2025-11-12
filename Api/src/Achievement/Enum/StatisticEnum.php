<?php

declare(strict_types=1);

namespace Mush\Achievement\Enum;

enum StatisticEnum: string
{
    case CAT_CUDDLED = 'cat_cuddled';
    case COFFEE_TAKEN = 'coffee_taken';
    case COOKED_TAKEN = 'cooked_taken';
    case DOOR_REPAIRED = 'door_repaired';
    case EXPLO_FEED = 'explo_feed';
    case EXPLORER = 'explorer';
    case BACK_TO_ROOT = 'back_to_root';
    case CAMERA_INSTALLED = 'camera_installed';
    case EXTINGUISH_FIRE = 'extinguish_fire';
    case GAGGED = 'gagged';
    case GIVE_MISSION = 'give_mission';
    case NEW_PLANTS = 'new_plants';
    case GAME_WITHOUT_SLEEP = 'game_without_sleep';
    case PLANET_SCANNED = 'planet_scanned';
    case SIGNAL_EQUIP = 'signal_equip';
    case SIGNAL_FIRE = 'signal_fire';
    case SUCCEEDED_INSPECTION = 'succeeded_inspection';
    case ANDIE = 'andie';
    case CHUN = 'chun';
    case KUAN_TI = 'kuan_ti';
    case CHAO = 'chao';
    case ELEESHA = 'eleesha';
    case FINOLA = 'finola';
    case FRIEDA = 'frieda';
    case HUA = 'hua';
    case JANICE = 'janice';
    case JIN_SU = 'jin_su';
    case CONTRIBUTIONS = 'contributions';
    case IAN = 'ian';
    case STEPHEN = 'stephen';
    case DEREK = 'derek';
    case GIOELE = 'gioele';
    case DAILY_ORDER = 'daily_order';
    case PAOLA = 'paola';
    case RALUCA = 'raluca';
    case ROLAND = 'roland';
    case TERRENCE = 'terrence';
    case EDEN = 'eden';
    case MUSH_CYCLES = 'mush_cycles';
    case EDEN_CONTAMINATED = 'eden_contaminated';
    case POLITICIAN = 'politician';
    case HUNTER_DOWN = 'hunter_down';
    case LIKES = 'likes';
    case SURGEON = 'surgeon';
    case BUTCHER = 'butcher';
    case COMMUNICATION_EXPERT = 'communication_expert';
    case NULL = '';

    public static function fromOrNull(string $value): self
    {
        return self::tryFrom($value) ?? self::NULL;
    }
}
