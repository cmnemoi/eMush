<?php

declare(strict_types=1);

namespace Mush\Equipment\Enum;

enum WeaponEventEnum: string
{
    // Blaster
    case BLASTER_SUCCESSFUL_SHOT = 'blaster_successful_shot';
    case BLASTER_TARGET_HEADSHOT = 'blaster_target_headshot';
    case BLASTER_TARGET_RANDOM_INJURY = 'blaster_target_random_injury';
    case BLASTER_SHOOTER_PLUS_2_MAX_DAMAGE_20_RANDOM_INJURY_TO_TARGET = 'blaster_shooter_plus_2_max_damage_20_random_injury_to_target';
    case BLASTER_SHOOTER_PLUS_1_DAMAGE_TARGET_DAMAGED_EARS = 'blaster_shooter_plus_1_damage_target_damaged_ears';
    case BLASTER_SHOOTER_PLUS_2_DAMAGE_TARGET_30_TORN_TONGUE_TARGET_30_BURST_NOSE_TARGET_30_OPEN_AIR_BRAIN_TARGET_30_HEAD_TRAUMA = 'blaster_shooter_plus_2_damage_target_30_torn_tongue_target_30_burst_nose_target_30_open_air_brain_target_30_head_trauma';
    case BLASTER_SHOOTER_PLUS_1_DAMAGE_TARGET_REMOVE_2_AP = 'blaster_shooter_plus_1_damage_target_remove_2_ap';
    case BLASTER_FAILED_SHOT = 'blaster_failed_shot';
    case BLASTER_BREAK_WEAPON = 'blaster_break_weapon';
    case BLASTER_SHOOTER_DROP_WEAPON = 'blaster_shooter_drop_weapon';
    case BLASTER_SHOOTER_MINUS_1_AP_SHOOTER_DROP_WEAPON_SHOOTER_RANDOM_INJURY = 'blaster_shooter_minus_1_ap_shooter_drop_weapon_shooter_random_injury';
    case BLASTER_SHOOTER_MINUS_1_AP_BREAK_WEAPON = 'blaster_shooter_minus_1_ap_break_weapon';
    case BLASTER_SHOOTER_MINUS_1_AP = 'blaster_shooter_minus_1_ap';

    // Natamy Rifle
    case NATAMY_RIFLE_SUCCESSFUL_SHOT = 'natamy_rifle_successful_shot';
    case NATAMY_RIFLE_HEADSHOT = 'natamy_rifle_headshot';

    public function toString(): string
    {
        return $this->value;
    }
}
