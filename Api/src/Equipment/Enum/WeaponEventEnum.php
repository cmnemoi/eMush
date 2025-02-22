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
    case KNIFE_BREAK_WEAPON = 'knife_break_weapon';
    case KNIFE_BREAK_WEAPON_SHOOTER_TORN_TONGUE = 'knife_break_weapon_shooter_torn_tongue';
    case KNIFE_SHOOTER_BRUISED_SHOULDER = 'knife_shooter_bruised_shoulder';
    case KNIFE_SHOOTER_DROP_WEAPON = 'knife_shooter_drop_weapon';
    case KNIFE_SHOOTER_MINUS_2_AP = 'knife_shooter_minus_2_ap';

    // Old Faithful
    case OLD_FAITHFUL_SUCCESSFUL_SHOT = 'old_faithful_successful_shot';
    case OLD_FAITHFUL_FAILED_SHOT = 'old_faithful_failed_shot';
    case OLD_FAITHFUL_HEADSHOT = 'old_faithful_headshot';
    case OLD_FAITHFUL_SHOOTER_PLUS_2_MAX_DAMAGE_SHOOTER_MINUS_1_AP_TARGET_CRITICAL_HAEMORRHAGE_40_PERCENTS_TARGET_RANDOM_INJURY = 'old_faithful_shooter_plus_2_max_damage_shooter_minus_1_ap_target_critical_haemorrhage_40_percents_target_random_injury';
    case OLD_FAITHFUL_TARGET_MINUS_1AP = 'old_faithful_target_minus_1ap';
    case OLD_FAITHFUL_HEADSHOT_2 = 'old_faithful_headshot_2';
    case OLD_FAITHFUL_TARGET_MASHED_FOOT = 'old_faithful_target_mashed_foot';
    case OLD_FAITHFUL_TARGET_BROKEN_SHOULDER_TARGET_CRITICAL_HAEMORRHAGE_40_PERCENTS_TARGET_HAEMORRHAGE_40_PERCENTS = 'old_faithful_target_broken_shoulder_target_critical_haemorrhage_40_percents_target_haemorrhage_40_percents';
    case OLD_FAITHFUL_BREAK_WEAPON = 'old_faithful_break_weapon';
    case OLD_FAITHFUL_SHOOTER_BURNT_HAND = 'old_faithful_shooter_burnt_hand';
    case OLD_FAITHFUL_SHOOTER_BROKEN_SHOULDER = 'old_faithful_shooter_broken_shoulder';
    case OLD_FAITHFUL_SHOOTER_MASHED_FOOT = 'old_faithful_shooter_mashed_foot';
    case OLD_FAITHFUL_DROP_WEAPON = 'old_faithful_drop_weapon';
    case OLD_FAITHFUL_SHOOTER_PLUS_2_DAMAGE = 'old_faithful_shooter_plus_2_damage';

    // Lizaro Jungle
    case LIZARO_JUNGLE_SUCCESSFUL_SHOT = 'lizaro_jungle_successful_shot';
    case LIZARO_JUNGLE_FAILED_SHOT = 'lizaro_jungle_failed_shot';
    case LIZARO_JUNGLE_HEADSHOT = 'lizaro_jungle_headshot';
    case LIZARO_JUNGLE_SHOOTER_PLUS_2_MAX_DAMAGE_SHOOTER_MINUS_1_AP_TARGET_CRITICAL_HAEMORRHAGE_40_PERCENTS_TARGET_RANDOM_INJURY = 'lizaro_jungle_shooter_plus_2_max_damage_shooter_minus_1_ap_target_critical_haemorrhage_40_percents_target_random_injury';
    case LIZARO_JUNGLE_TARGET_MINUS_1AP = 'lizaro_jungle_target_minus_1ap';
    case LIZARO_JUNGLE_HEADSHOT_2 = 'lizaro_jungle_headshot_2';
    case LIZARO_JUNGLE_TARGET_MASHED_FOOT = 'lizaro_jungle_target_mashed_foot';
    case LIZARO_JUNGLE_TARGET_BROKEN_SHOULDER_TARGET_CRITICAL_HAEMORRHAGE_40_PERCENTS_TARGET_HAEMORRHAGE_40_PERCENTS = 'lizaro_jungle_target_broken_shoulder_target_critical_haemorrhage_40_percents_target_haemorrhage_40_percents';
    case LIZARO_JUNGLE_BREAK_WEAPON = 'lizaro_jungle_break_weapon';
    case LIZARO_JUNGLE_SHOOTER_BURNT_HAND = 'lizaro_jungle_shooter_burnt_hand';
    case LIZARO_JUNGLE_SHOOTER_BROKEN_SHOULDER = 'lizaro_jungle_shooter_broken_shoulder';
    case LIZARO_JUNGLE_SHOOTER_MASHED_FOOT = 'lizaro_jungle_shooter_mashed_foot';
    case LIZARO_JUNGLE_DROP_WEAPON = 'lizaro_jungle_drop_weapon';
    case LIZARO_JUNGLE_SHOOTER_PLUS_2_DAMAGE = 'lizaro_jungle_shooter_plus_2_damage';

    // Bare Hands
    case BARE_HANDS_SUCCESSFUL_HIT = 'bare_hands_successful_hit';
    case BARE_HANDS_FAILED_HIT = 'bare_hands_failed_hit';
    case BARE_HANDS_PLUS_1_DAMAGE = 'bare_hands_plus_1_damage';
    case BARE_HANDS_TARGET_BURST_NOSE_TARGET_10_PERCENTS = 'bare_hands_target_burst_nose_10_percent';
    case BARE_HANDS_FUMBLE = 'bare_hands_fumble';

    // Grenade

    case GRENADE_SUCCESSFUL_THROW_SPLASH_DAMAGE_ALL = 'grenade_successful_throw_splash_damage_all';
    case GRENADE_CRITICAL_THROW_SPLASH_DAMAGE_ALL_BREAK_ITEMS_SPLASH_WOUNDS = 'grenade_critical_throw_splash_damage_all_break_items_splash_wounds';

    // grenade cannot fail throwing in mush Twinoid/current eMush. There is unused data for grenade fails, but implementing it should be discussed first.
    case GRENADE_FAILURE_PLACEHOLDER = 'grenade_failure_placeholder';

    public function toString(): string
    {
        return $this->value;
    }
}
