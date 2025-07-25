<?php

namespace Mush\Disease\ConfigData;

use Mush\Disease\Enum\DiseaseCauseEnum;
use Mush\Disease\Enum\InjuryEnum;

/** @codeCoverageIgnore */
class DiseaseCauseConfigData
{
    public static array $dataArray = [
        [
            'name' => 'alien_fruit_default',
            'causeName' => 'alien_fruit',
            'diseases' => ['cat_allergy' => 1,
                'mush_allergy' => 1,
                'sepsis' => 1,
                'slight_nausea' => 1,
                'smallpox' => 1,
                'syphilis' => 1,
                'ailurophobia' => 1,
                'coprolalia' => 1,
                'spleen' => 1,
                'weapon_phobia' => 1,
                'chronic_vertigo' => 1,
                'paranoia' => 1,
                'acid_reflux' => 2,
                'skin_inflammation' => 2,
                'agoraphobia' => 2,
                'chronic_migraine' => 2,
                'vertigo' => 2,
                'depression' => 2,
                'psychotic_episodes' => 2,
                'crabism' => 4,
                'black_bite' => 4,
                'cold' => 4,
                'extreme_tinnitus' => 4,
                'food_poisoning' => 4,
                'fungic_infection' => 4,
                'rejuvenation' => 4,
                'rubella' => 4,
                'sinus_storm' => 4,
                'space_rabies' => 4,
                'vitamin_deficiency' => 4,
                'flu' => 8,
                'gastroenteritis' => 8,
                'migraine' => 8,
                'tapeworm' => 8, ],
        ],
        [
            'name' => 'perished_food_default',
            'causeName' => 'perished_food',
            'diseases' => ['food_poisoning' => 1],
        ],
        [
            'name' => 'cycle_low_morale_default',
            'causeName' => 'cycle_low_morale',
            'diseases' => ['mush_allergy' => 1,
                'cat_allergy' => 1,
                'fungic_infection' => 2,
                'sinus_storm' => 2,
                'vitamin_deficiency' => 4,
                'acid_reflux' => 4,
                'migraine' => 4,
                'gastroenteritis' => 8,
                'cold' => 8,
                'slight_nausea' => 8,
                'depression' => 32, ],
        ],
        [
            'name' => 'cycle_default',
            'causeName' => 'cycle',
            'diseases' => ['mush_allergy' => 1,
                'cat_allergy' => 1,
                'fungic_infection' => 2,
                'sinus_storm' => 2,
                'vitamin_deficiency' => 4,
                'acid_reflux' => 4,
                'migraine' => 4,
                'gastroenteritis' => 8,
                'cold' => 8,
                'slight_nausea' => 8, ],
        ],
        [
            'name' => 'make_sick_default',
            'causeName' => 'make_sick',
            'diseases' => ['cold' => 1,
                'fungic_infection' => 1,
                'flu' => 1,
                'extreme_tinnitus' => 1, ],
        ],
        [
            'name' => 'fake_disease_default',
            'causeName' => 'fake_disease',
            'diseases' => ['cat_allergy' => 1,
                'mush_allergy' => 1,
                'sepsis' => 1,
                'slight_nausea' => 1,
                'smallpox' => 1,
                'syphilis' => 1,
                'ailurophobia' => 1,
                'coprolalia' => 1,
                'weapon_phobia' => 1,
                'chronic_vertigo' => 1,
                'paranoia' => 1,
                'acid_reflux' => 1,
                'skin_inflammation' => 1,
                'agoraphobia' => 1,
                'chronic_migraine' => 1,
                'vertigo' => 1,
                'depression' => 1,
                'psychotic_episodes' => 1,
                'crabism' => 1,
                'black_bite' => 1,
                'cold' => 1,
                'extreme_tinnitus' => 1,
                'fungic_infection' => 1,
                'rejuvenation' => 1,
                'rubella' => 1,
                'sinus_storm' => 1,
                'space_rabies' => 1,
                'vitamin_deficiency' => 1,
                'flu' => 1,
                'migraine' => 1,
                'tapeworm' => 1,
                'critical_haemorrhage' => 1,
                'haemorrhage' => 1,
                'busted_arm_joint' => 1,
                'bruised_shoulder' => 1,
                'punctured_lung' => 1,
                'burnt_hand' => 1,
                'missing_finger' => 1,
                'broken_finger' => 1,
                'mashed_arms' => 1,
                'torn_tongue' => 1,
                'open_air_brain' => 1,
                'mashed_foot' => 1,
                'mashed_legs' => 1,
                'inner_ear_damaged' => 1,
                'broken_shoulder' => 1,
                'broken_foot' => 1,
                'mashed_hand' => 1,
                'broken_ribs' => 1,
                'burnt_arms' => 1,
                'busted_shoulder' => 1,
                'burns_90_of_body' => 1,
                'damaged_ears' => 1,
                'broken_leg' => 1, ],
        ],
        [
            'name' => 'surgery_default',
            'causeName' => 'surgery',
            'diseases' => ['sepsis' => 1],
        ],
        [
            'name' => 'cat_allergy_default',
            'causeName' => 'cat_allergy',
            'diseases' => ['burnt_arms' => 1,
                'burnt_hand' => 1, ],
        ],
        [
            'name' => 'infection_default',
            'causeName' => 'infection',
            'diseases' => ['flu' => 50,
                'gastroenteritis' => 20,
                'fungic_infection' => 15,
                'migraine' => 10,
                'mush_allergy' => 5, ],
        ],
        [
            'name' => 'sex_default',
            'causeName' => 'sex',
            'diseases' => ['flu' => 1,
                'gastroenteritis' => 1,
                'skin_inflammation' => 1, ],
        ],
        [
            'name' => 'trauma_default',
            'causeName' => 'trauma',
            'diseases' => ['migraine' => 30,
                'gastroenteritis' => 30,
                'chronic_migraine' => 6,
                'psychotic_episodes' => 6,
                'weapon_phobia' => 6,
                'paranoia' => 6,
                'crabism' => 6,
                'coprolalia' => 6,
                'depression' => 6,
                'agoraphobia' => 3,
                'chronic_vertigo' => 3,
                'spleen' => 1, ],
        ],
        [
            'name' => 'contact_default',
            'causeName' => 'contact',
            'diseases' => ['flu' => 1,
                'gastroenteritis' => 1,
                'skin_inflammation' => 1, ],
        ],
        [
            'name' => 'critical_fail_knife_default',
            'causeName' => 'critical_fail_knife',
            'diseases' => ['torn_tongue' => 1,
                'bruised_shoulder' => 1, ],
        ],
        [
            'name' => 'critical_success_knife_default',
            'causeName' => 'critical_success_knife',
            'diseases' => ['critical_haemorrhage' => 30,
                'haemorrhage' => 20,
                'busted_arm_joint' => 14,
                'bruised_shoulder' => 7,
                'punctured_lung' => 5,
                'burnt_hand' => 3,
                'missing_finger' => 2,
                'broken_finger' => 2,
                'mashed_arms' => 2,
                'torn_tongue' => 2,
                'open_air_brain' => 2,
                'mashed_foot' => 2,
                'mashed_legs' => 2,
                'inner_ear_damaged' => 2,
                'broken_shoulder' => 2,
                'broken_foot' => 2,
                'mashed_hand' => 1,
                'broken_ribs' => 1,
                'burnt_arms' => 1,
                'busted_shoulder' => 1,
                'burns_90_of_body' => 1,
                'damaged_ears' => 1,
                'broken_leg' => 1, ],
        ],
        [
            'name' => 'critical_fail_blaster_default',
            'causeName' => 'critical_fail_blaster',
            'diseases' => ['broken_leg' => 1,
                'broken_shoulder' => 1, ],
        ],
        [
            'name' => 'critical_success_blaster_default',
            'causeName' => 'critical_success_blaster',
            'diseases' => ['damaged_ears' => 10,
                'critical_haemorrhage' => 2,
                'open_air_brain' => 2,
                'burns_90_of_body' => 2,
                'torn_tongue' => 2,
                'punctured_lung' => 1,
                'haemorrhage' => 1,
                'broken_shoulder' => 1,
                'head_trauma' => 1,
                'burns_50_of_body' => 1, ],
        ],
        [
            'name' => 'alien_fight_default',
            'causeName' => 'alien_fight',
            'diseases' => [
                'flu' => 1,
                'syphilis' => 1,
                'black_bite' => 1,
                'rejuvenation' => 1,
                'ailurophobia' => 1,
                'space_rabies' => 1,
                'sepsis' => 1,
            ],
        ],
        [
            'name' => 'exploration_default',
            'causeName' => 'exploration',
            'diseases' => [
                'migraine' => 1,
                'acid_reflux' => 1,
                'flu' => 1,
                'rubella' => 1,
                'gastroenteritis' => 1,
                'smallpox' => 1,
                'syphilis' => 1,
                'black_bite' => 1,
                'rejuvenation' => 1,
                'skin_inflammation' => 1,
                'slight_nausea' => 1,
            ],
        ],
        [
            'name' => DiseaseCauseEnum::RANDOM_INJURY . '_default',
            'causeName' => DiseaseCauseEnum::RANDOM_INJURY,
            'diseases' => [
                InjuryEnum::BURNS_50_OF_BODY => 1,
                InjuryEnum::BURNS_90_OF_BODY => 1,
                InjuryEnum::BROKEN_FINGER => 1,
                InjuryEnum::BROKEN_FOOT => 1,
                InjuryEnum::BROKEN_LEG => 1,
                InjuryEnum::BROKEN_RIBS => 1,
                InjuryEnum::BROKEN_SHOULDER => 1,
                InjuryEnum::BRUISED_SHOULDER => 1,
                InjuryEnum::BURNT_ARMS => 1,
                InjuryEnum::BURNT_HAND => 1,
                InjuryEnum::BURST_NOSE => 1,
                InjuryEnum::BUSTED_ARM_JOINT => 1,
                InjuryEnum::BUSTED_SHOULDER => 1,
                InjuryEnum::CRITICAL_HAEMORRHAGE => 1,
                InjuryEnum::HAEMORRHAGE => 1,
                InjuryEnum::MINOR_HAEMORRHAGE => 1,
                InjuryEnum::DAMAGED_EARS => 1,
                InjuryEnum::DESTROYED_EARS => 1,
                InjuryEnum::DYSFUNCTIONAL_LIVER => 1,
                InjuryEnum::HEAD_TRAUMA => 1,
                InjuryEnum::IMPLANTED_BULLET => 1,
                InjuryEnum::INNER_EAR_DAMAGED => 1,
                InjuryEnum::MASHED_FOOT => 1,
                InjuryEnum::MASHED_HAND => 1,
                InjuryEnum::MISSING_FINGER => 1,
                InjuryEnum::OPEN_AIR_BRAIN => 1,
                InjuryEnum::PUNCTURED_LUNG => 1,
                InjuryEnum::MASHED_ARMS => 1,
                InjuryEnum::MASHED_LEGS => 1,
                InjuryEnum::TORN_TONGUE => 1,
            ],
        ],
    ];

    public static function getByName(string $name): array
    {
        $data = current(array_filter(self::$dataArray, static fn (array $data) => $data['name'] === $name));

        if (!$data) {
            throw new \Exception(\sprintf('Disease cause config %s not found', $name));
        }

        return $data;
    }
}
