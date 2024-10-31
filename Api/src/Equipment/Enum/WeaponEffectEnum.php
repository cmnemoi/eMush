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

    // one shot effects
    case BLASTER_ONE_SHOT = 'blaster_one_shot';
    case NATAMY_RIFLE_ONE_SHOT = 'natamy_rifle_one_shot';

    case ADD_ONE_DAMAGE = 'add_one_damage';
    case ADD_TWO_DAMAGE = 'add_two_damage';
    case ADD_TWO_MAX_DAMAGE = 'add_two_max_damage';
    case INFLICT_BURST_NOSE_INJURY_TO_TARGET_30_PERCENTS = 'inflict_burst_nose_injury_to_target_30_percents';
    case INFLICT_HEAD_TRAUMA_INJURY_TO_TARGET_30_PERCENTS = 'inflict_head_trauma_injury_to_target_30_percents';
    case INFLICT_MASHED_EAR_INJURY_TO_TARGET = 'inflict_mashed_ear_injury_to_target';
    case INFLICT_OPEN_AIR_BRAIN_INJURY_TO_TARGET_30_PERCENTS = 'inflict_open_air_brain_injury_to_target_30_percents';
    case INFLICT_RANDOM_INJURY_TO_SHOOTER = 'inflict_random_injury_to_shooter';
    case INFLICT_RANDOM_INJURY_TO_TARGET = 'inflict_random_injury_to_target';
    case INFLICT_RANDOM_INJURY_TO_TARGET_20_PERCENTS = 'inflict_random_injury_to_target_20_percents';
    case INFLICT_TORN_TONGUE_INJURY_TO_TARGET_30_PERCENTS = 'inflict_torn_tongue_injury_to_target_30_percents';
    case REMOVE_ONE_ACTION_POINT_TO_SHOOTER = 'remove_one_action_point_to_shooter';
    case REMOVE_TWO_ACTION_POINTS_TO_TARGET = 'remove_two_action_points_to_target';

    public function toString(): string
    {
        return $this->value;
    }
}
