<?php

declare(strict_types=1);

namespace Mush\Project\ConfigData;

use Mush\Game\Enum\SkillEnum;
use Mush\Project\Enum\ProjectName;
use Mush\Project\Enum\ProjectType;

/**
 * @codeCoverageIgnore
 */
abstract class ProjectConfigData
{
    public static function getAll(): array
    {
        return [
            [
                'name' => ProjectName::PILGRED,
                'type' => ProjectType::PILGRED,
                'efficiency' => 1,
                'bonusSkills' => [SkillEnum::PHYSICIST, SkillEnum::TECHNICIAN],
                'activationRate' => 100,
                'activationEvents' => [],
                'modifierConfigs' => [],
            ],
            [
                'name' => ProjectName::FIRE_SENSOR,
                'type' => ProjectType::NERON_PROJECT,
                'efficiency' => 18,
                'bonusSkills' => [SkillEnum::RADIO_EXPERT, SkillEnum::FIREFIGHTER],
                'activationRate' => 100,
                'activationEvents' => [],
                'modifierConfigs' => [],
            ],
            [
                'name' => ProjectName::DOOR_SENSOR,
                'type' => ProjectType::NERON_PROJECT,
                'efficiency' => 18,
                'bonusSkills' => [SkillEnum::RADIO_EXPERT, SkillEnum::ROBOTICS_EXPERT],
                'activationRate' => 100,
                'activationEvents' => [],
                'modifierConfigs' => [],
            ],
            [
                'name' => ProjectName::EQUIPMENT_SENSOR,
                'type' => ProjectType::NERON_PROJECT,
                'efficiency' => 18,
                'bonusSkills' => [SkillEnum::RADIO_EXPERT, SkillEnum::TECHNICIAN],
                'activationRate' => 100,
                'activationEvents' => [],
                'modifierConfigs' => [],
            ],
            [
                'name' => ProjectName::PLASMA_SHIELD,
                'type' => ProjectType::NERON_PROJECT,
                'efficiency' => 1,
                'bonusSkills' => [SkillEnum::TECHNICIAN, SkillEnum::PHYSICIST],
                'activationRate' => 100,
                'activationEvents' => [],
                'modifierConfigs' => [
                    'modifier_for_daedalus_set_daedalus_shield_to_50',
                    'modifier_for_daedalus_+5shield_on_new_cycle',
                ],
            ],
            [
                'name' => ProjectName::HEAT_LAMP,
                'type' => ProjectType::NERON_PROJECT,
                'efficiency' => 3,
                'bonusSkills' => [SkillEnum::TECHNICIAN, SkillEnum::BOTANIST],
                'activationRate' => 50,
                'activationEvents' => [],
                'modifierConfigs' => [],
            ],
            [
                'name' => ProjectName::TRAIL_REDUCER,
                'type' => ProjectType::NERON_PROJECT,
                'efficiency' => 6,
                'bonusSkills' => [SkillEnum::PILOT, SkillEnum::TECHNICIAN],
                'activationRate' => 100,
                'activationEvents' => [],
                'modifierConfigs' => [
                    'modifier_for_daedalus_-25percentage_following_hunters_on_daedalus_travel',
                ],
            ],
            [
                'name' => ProjectName::CHIPSET_ACCELERATION,
                'type' => ProjectType::NERON_PROJECT,
                'efficiency' => 6,
                'bonusSkills' => [SkillEnum::ASTROPHYSICIST, SkillEnum::IT_EXPERT],
                'activationRate' => 100,
                'activationEvents' => [],
                'modifierConfigs' => [
                    'modifier_for_daedalus_-1actionPoint_on_action_scan_planet',
                ],
            ],
            [
                'name' => ProjectName::DISMANTLING,
                'type' => ProjectType::NERON_PROJECT,
                'efficiency' => 12,
                'bonusSkills' => [SkillEnum::TECHNICIAN, SkillEnum::ROBOTICS_EXPERT],
                'activationRate' => 100,
                'activationEvents' => [
                    '5_metal_scraps_in_engine_room',
                ],
                'modifierConfigs' => [],
            ],
            [
                'name' => ProjectName::EXTRA_HYDROPONPOTS,
                'type' => ProjectType::NERON_PROJECT,
                'efficiency' => 6,
                'bonusSkills' => [SkillEnum::TECHNICIAN, SkillEnum::BOTANIST],
                'activationRate' => 100,
                'activationEvents' => [
                    '3_hydropot_in_garden',
                ],
                'modifierConfigs' => [],
            ],
            [
                'name' => ProjectName::AUXILIARY_TERMINAL,
                'type' => ProjectType::NERON_PROJECT,
                'efficiency' => 6,
                'bonusSkills' => [SkillEnum::RADIO_EXPERT, SkillEnum::IT_EXPERT],
                'activationRate' => 100,
                'activationEvents' => [
                    '1_auxiliary_neron_core_in_medlab',
                    '1_auxiliary_neron_core_in_engine_room',
                ],
                'modifierConfigs' => [],
            ],
        ];
    }
}
