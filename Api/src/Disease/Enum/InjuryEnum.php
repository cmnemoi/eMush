<?php

namespace Mush\Disease\Enum;

// every injury in this enum can be given randomly by weapons. Be careful
enum InjuryEnum: string
{
    case BURNS_50_OF_BODY = 'burns_50_of_body';
    case BURNS_90_OF_BODY = 'burns_90_of_body';
    case BROKEN_FINGER = 'broken_finger';
    case BROKEN_FOOT = 'broken_foot';
    case BROKEN_LEG = 'broken_leg';
    case BROKEN_RIBS = 'broken_ribs';
    case BROKEN_SHOULDER = 'broken_shoulder';
    case BRUISED_SHOULDER = 'bruised_shoulder';
    case BURNT_ARMS = 'burnt_arms';
    case BURNT_HAND = 'burnt_hand';
    case BURST_NOSE = 'burst_nose';
    case BUSTED_ARM_JOINT = 'busted_arm_joint';
    case BUSTED_SHOULDER = 'busted_shoulder';
    case CRITICAL_HAEMORRHAGE = 'critical_haemorrhage';
    case HAEMORRHAGE = 'haemorrhage';
    case DAMAGED_EARS = 'damaged_ears';
    case DESTROYED_EARS = 'destroyed_ears';
    case DYSFUNCTIONAL_LIVER = 'dysfunctional_liver';
    case HEAD_TRAUMA = 'head_trauma';
    case IMPLANTED_BULLET = 'implanted_bullet';
    case INNER_EAR_DAMAGED = 'inner_ear_damaged';
    case MASHED_FOOT = 'mashed_foot';
    case MASHED_HAND = 'mashed_hand';
    case MISSING_FINGER = 'missing_finger';
    case OPEN_AIR_BRAIN = 'open_air_brain';
    case PUNCTURED_LUNG = 'punctured_lung';
    case MASHED_ARMS = 'mashed_arms';
    case MASHED_LEGS = 'mashed_legs';
    case TORN_TONGUE = 'torn_tongue';

    public function toConfigKey(string $configKey): string
    {
        return $this->value . '_' . $configKey;
    }

    public function toString(): string
    {
        return $this->value;
    }
}
