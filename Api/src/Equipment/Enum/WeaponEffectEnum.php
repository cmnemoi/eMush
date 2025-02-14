<?php

declare(strict_types=1);

namespace Mush\Equipment\Enum;

enum WeaponEffectEnum: string
{
    // general effects
    case BREAK_WEAPON = 'break_weapon';
    case DROP_WEAPON = 'drop_weapon';
    case INFLICT_INJURY = 'inflict_injury';
    case INFLICT_RANDOM_INJURY = 'inflict_random_injury';
    case MODIFY_DAMAGE = 'modify_damage';
    case MODIFY_MAX_DAMAGE = 'modify_max_damage';
    case ONE_SHOT = 'one_shot';
    case REMOVE_ACTION_POINTS = 'remove_action_points';
    case MULTIPLY_DAMAGE_ON_MUSH_TARGET = 'multiply_damage_on_mush_target';

    // one shot effects
    case BLASTER_ONE_SHOT = 'blaster_one_shot';
    case BIG_GUN_ONE_SHOT = 'big_gun_one_shot';
    case NATAMY_RIFLE_INJURY_ONE_SHOT = 'natamy_rifle_injury_one_shot';
    case OLD_FAITHFUL_INJURY_ONE_SHOT = 'old_faithful_injury_one_shot';
    case LIZARO_JUNGLE_INJURY_ONE_SHOT = 'lizaro_jungle_injury_one_shot';

    // modify damage effects
    case ADD_ONE_DAMAGE = 'add_one_damage';
    case ADD_TWO_DAMAGE = 'add_two_damage';

    // modify max damage effects
    case ADD_TWO_MAX_DAMAGE = 'add_two_max_damage';

    // inflict injury effects
    case INFLICT_BURST_NOSE_INJURY_TO_TARGET_30_PERCENTS = 'inflict_burst_nose_injury_to_target_30_percents';
    case INFLICT_HEAD_TRAUMA_INJURY_TO_TARGET_30_PERCENTS = 'inflict_head_trauma_injury_to_target_30_percents';
    case INFLICT_MASHED_EAR_INJURY_TO_TARGET = 'inflict_mashed_ear_injury_to_target';
    case INFLICT_OPEN_AIR_BRAIN_INJURY_TO_TARGET_30_PERCENTS = 'inflict_open_air_brain_injury_to_target_30_percents';
    case INFLICT_CRITICAL_HAEMORRHAGE_INJURY_TO_TARGET_10_PERCENTS = 'inflict_critical_haemorrhage_injury_to_target_10_percents';
    case INFLICT_HAEMORRHAGE_INJURY_TO_TARGET_40_PERCENTS = 'inflict_haemorrhage_injury_to_target_40_percents';
    case INFLICT_CRITICAL_HAEMORRHAGE_INJURY_TO_TARGET_40_PERCENTS = 'inflict_critical_haemorrhage_injury_to_target_40_percents';
    case INFLICT_MASHED_FOOT_INJURY_TO_TARGET = 'inflict_mashed_foot_injury_to_target';
    case INFLICT_BROKEN_SHOULDER_INJURY_TO_TARGET = 'inflict_broken_shoulder_injury_to_target';
    case INFLICT_BURNT_HAND_INJURY_TO_SHOOTER = 'inflict_burnt_hand_injury_to_shooter';
    case INFLICT_BROKEN_SHOULDER_INJURY_TO_SHOOTER = 'inflict_broken_shoulder_injury_to_shooter';
    case INFLICT_MASHED_FOOT_TO_SHOOTER = 'inflict_mashed_foot_to_shooter';

    // inflict random injury effects
    case INFLICT_RANDOM_INJURY_TO_SHOOTER = 'inflict_random_injury_to_shooter';
    case INFLICT_RANDOM_INJURY_TO_TARGET = 'inflict_random_injury_to_target';
    case INFLICT_RANDOM_INJURY_TO_TARGET_20_PERCENTS = 'inflict_random_injury_to_target_20_percents';
    case INFLICT_TORN_TONGUE_INJURY_TO_TARGET_30_PERCENTS = 'inflict_torn_tongue_injury_to_target_30_percents';

    // remove action point effects
    case REMOVE_ONE_ACTION_POINT_TO_SHOOTER = 'remove_one_action_point_to_shooter';
    case REMOVE_TWO_ACTION_POINTS_TO_TARGET = 'remove_two_action_points_to_target';
    case REMOVE_ONE_ACTION_POINT_TO_TARGET = 'remove_one_action_point_to_target';

    // multiply damage on mush target effects
    case DOUBLE_DAMAGE_ON_MUSH_TARGET = 'double_damage_on_mush_target';

    public function toString(): string
    {
        return $this->value;
    }
}
