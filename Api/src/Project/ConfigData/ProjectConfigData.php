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
                'modifierConfigs' => [],
            ],
            [
                'name' => ProjectName::FIRE_SENSOR,
                'type' => ProjectType::NERON_PROJECT,
                'efficiency' => 18,
                'bonusSkills' => [SkillEnum::RADIO_EXPERT, SkillEnum::FIREFIGHTER],
                'activationRate' => 100,
                'modifierConfigs' => [],
            ],
            [
                'name' => ProjectName::DOOR_SENSOR,
                'type' => ProjectType::NERON_PROJECT,
                'efficiency' => 18,
                'bonusSkills' => [SkillEnum::RADIO_EXPERT, SkillEnum::ROBOTICS_EXPERT],
                'activationRate' => 100,
                'modifierConfigs' => [],
            ],
            [
                'name' => ProjectName::EQUIPMENT_SENSOR,
                'type' => ProjectType::NERON_PROJECT,
                'efficiency' => 18,
                'bonusSkills' => [SkillEnum::RADIO_EXPERT, SkillEnum::TECHNICIAN],
                'activationRate' => 100,
                'modifierConfigs' => [],
            ],
            [
                'name' => ProjectName::PLASMA_SHIELD,
                'type' => ProjectType::NERON_PROJECT,
                'efficiency' => 1,
                'bonusSkills' => [SkillEnum::TECHNICIAN, SkillEnum::PHYSICIST],
                'activationRate' => 100,
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
                'modifierConfigs' => [],
            ],
            [
                'name' => ProjectName::TRAIL_REDUCER,
                'type' => ProjectType::NERON_PROJECT,
                'efficiency' => 6,
                'bonusSkills' => [SkillEnum::PILOT, SkillEnum::TECHNICIAN],
                'activationRate' => 100,
                'modifierConfigs' => [
                    'modifier_for_daedalus_-25percentage_following_hunters_on_daedalus_travel',
                ],
            ],
            [
                'name' => ProjectName::CPU_OVERCLOCKING,
                'type' => ProjectType::NERON_PROJECT,
                'efficiency' => 6,
                'bonusSkills' => [SkillEnum::ASTROPHYSICIST, SkillEnum::IT_EXPERT],
                'modifierConfigs' => [
                    'modifier_for_daedalus_-1actionPoint_on_action_analyze_planet',
                ],
            ],
        ];
    }
}
