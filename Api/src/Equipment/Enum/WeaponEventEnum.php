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
    case NATAMY_RIFLE_FAILED_SHOT = 'natamy_rifle_failed_shot';
    case NATAMY_RIFLE_HEADSHOT = 'natamy_rifle_headshot';
    case NATAMY_RIFLE_SHOOTER_PLUS_2_MAX_DAMAGE_SHOOTER_MINUS_1_AP_TARGET_CRITICAL_HAEMORRHAGE_40_PERCENTS_TARGET_RANDOM_INJURY = 'natamy_rifle_shooter_plus_2_max_damage_shooter_minus_1_ap_target_critical_haemorrhage_40_percents_target_random_injury';
    case NATAMY_RIFLE_TARGET_MINUS_1AP = 'natamy_rifle_target_minus_1ap';
    case NATAMY_RIFLE_HEADSHOT_2 = 'natamy_rifle_headshot_2';
    case NATAMY_RIFLE_TARGET_MASHED_FOOT = 'natamy_rifle_target_mashed_foot';
    case NATAMY_RIFLE_TARGET_BROKEN_SHOULDER_TARGET_CRITICAL_HAEMORRHAGE_40_PERCENTS_TARGET_HAEMORRHAGE_40_PERCENTS = 'natamy_rifle_target_broken_shoulder_target_critical_haemorrhage_40_percents_target_haemorrhage_40_percents';
    case NATAMY_RIFLE_BREAK_WEAPON = 'natamy_rifle_break_weapon';
    case NATAMY_RIFLE_SHOOTER_BURNT_HAND = 'natamy_rifle_shooter_burnt_hand';
    case NATAMY_RIFLE_SHOOTER_BROKEN_SHOULDER = 'natamy_rifle_shooter_broken_shoulder';
    case NATAMY_RIFLE_SHOOTER_MASHED_FOOT = 'natamy_rifle_shooter_mashed_foot';
    case NATAMY_RIFLE_DROP_WEAPON = 'natamy_rifle_drop_weapon';
    case NATAMY_RIFLE_SHOOTER_PLUS_2_DAMAGE = 'natamy_rifle_shooter_plus_2_damage';

    // knife
    case KNIFE_SUCCESSFUL_HIT_10_MINOR_HAEMORRHAGE = 'knife_successful_hit_10_minor_haemorrhage';
    case KNIFE_PLUS_2_DAMAGE_RANDOM_INJURY = 'knife_plus_2_damage_random_injury';
    case KNIFE_PLUS_2_DAMAGE_50_CRITICAL_HAEMORRHAGE = 'knife_plus_2_damage_50_critical_haemorrhage';
    case KNIFE_PLUS_2_DAMAGE_50_CRITICAL_HAEMORRHAGE_RANDOM_INJURY = 'knife_plus_2_damage_50_critical_haemorrhage_random_injury';
    case KNIFE_PLUS_2_DAMAGE_60_CRITICAL_HAEMORRHAGE_BUSTED_ARM_JOINT = 'knife_plus_2_damage_60_critical_haemorrhage_busted_arm_joint';
    case KNIFE_INSTAGIB_BLED = 'knife_instagib_bled';
    case KNIFE_PLUS_2_DAMAGE_PUNCTURED_LUNG = 'knife_plus_2_damage_punctured_lung';
    case KNIFE_FAILED_HIT = 'knife_failed_hit';
    case KNIFE_DESTROY_WEAPON = 'knife_destroy_weapon';
    case KNIFE_DESTROY_WEAPON_SHOOTER_TORN_TONGUE = 'knife_destroy_weapon_shooter_torn_tongue';
    case KNIFE_SHOOTER_BRUISED_SHOULDER = 'knife_shooter_bruised_shoulder';
    case KNIFE_SHOOTER_DROP_WEAPON = 'knife_shooter_drop_weapon';
    case KNIFE_SHOOTER_MINUS_2_AP = 'knife_shooter_minus_2_ap';

    public function toString(): string
    {
        return $this->value;
    }
}
