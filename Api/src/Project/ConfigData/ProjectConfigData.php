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
                'spawnEquipmentConfigs' => [],
                'replaceEquipmentConfigs' => [],
            ],
            [
                'name' => ProjectName::FIRE_SENSOR,
                'type' => ProjectType::NERON_PROJECT,
                'efficiency' => 18,
                'bonusSkills' => [SkillEnum::RADIO_EXPERT, SkillEnum::FIREFIGHTER],
                'activationRate' => 100,
                'modifierConfigs' => [],
                'spawnEquipmentConfigs' => [],
                'replaceEquipmentConfigs' => [],
            ],
            [
                'name' => ProjectName::DOOR_SENSOR,
                'type' => ProjectType::NERON_PROJECT,
                'efficiency' => 18,
                'bonusSkills' => [SkillEnum::RADIO_EXPERT, SkillEnum::ROBOTICS_EXPERT],
                'activationRate' => 100,
                'modifierConfigs' => [],
                'spawnEquipmentConfigs' => [],
                'replaceEquipmentConfigs' => [],
            ],
            [
                'name' => ProjectName::EQUIPMENT_SENSOR,
                'type' => ProjectType::NERON_PROJECT,
                'efficiency' => 18,
                'bonusSkills' => [SkillEnum::RADIO_EXPERT, SkillEnum::TECHNICIAN],
                'activationRate' => 100,
                'modifierConfigs' => [],
                'spawnEquipmentConfigs' => [],
                'replaceEquipmentConfigs' => [],
            ],
            [
                'name' => ProjectName::PLASMA_SHIELD,
                'type' => ProjectType::NERON_PROJECT,
                'efficiency' => 1,
                'bonusSkills' => [SkillEnum::TECHNICIAN, SkillEnum::PHYSICIST],
                'activationRate' => 100,
                'modifierConfigs' => [
                    'modifier_for_daedalus_+5shield_on_new_cycle',
                ],
                'spawnEquipmentConfigs' => [],
                'replaceEquipmentConfigs' => [],
            ],
            [
                'name' => ProjectName::HEAT_LAMP,
                'type' => ProjectType::NERON_PROJECT,
                'efficiency' => 3,
                'bonusSkills' => [SkillEnum::TECHNICIAN, SkillEnum::BOTANIST],
                'activationRate' => 50,
                'modifierConfigs' => [],
                'spawnEquipmentConfigs' => [],
                'replaceEquipmentConfigs' => [],
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
                'spawnEquipmentConfigs' => [],
                'replaceEquipmentConfigs' => [],
            ],
            [
                'name' => ProjectName::CHIPSET_ACCELERATION,
                'type' => ProjectType::NERON_PROJECT,
                'efficiency' => 6,
                'bonusSkills' => [SkillEnum::ASTROPHYSICIST, SkillEnum::IT_EXPERT],
                'activationRate' => 100,
                'modifierConfigs' => [
                    'modifier_for_daedalus_-1actionPoint_on_action_scan_planet',
                ],
                'spawnEquipmentConfigs' => [],
                'replaceEquipmentConfigs' => [],
            ],
            [
                'name' => ProjectName::DISMANTLING,
                'type' => ProjectType::NERON_PROJECT,
                'efficiency' => 12,
                'bonusSkills' => [SkillEnum::TECHNICIAN, SkillEnum::ROBOTICS_EXPERT],
                'activationRate' => 100,
                'modifierConfigs' => [],
                'replaceEquipmentConfigs' => [],
                'spawnEquipmentConfigs' => [
                    '5_metal_scraps_in_engine_room',
                ],
            ],
            [
                'name' => ProjectName::EXTRA_HYDROPONPOTS,
                'type' => ProjectType::NERON_PROJECT,
                'efficiency' => 6,
                'bonusSkills' => [SkillEnum::TECHNICIAN, SkillEnum::BOTANIST],
                'activationRate' => 100,
                'modifierConfigs' => [],
                'spawnEquipmentConfigs' => [
                    '3_hydropots_in_hydroponic_garden',
                ],
                'replaceEquipmentConfigs' => [],
            ],
            [
                'name' => ProjectName::AUXILIARY_TERMINAL,
                'type' => ProjectType::NERON_PROJECT,
                'efficiency' => 6,
                'bonusSkills' => [SkillEnum::RADIO_EXPERT, SkillEnum::IT_EXPERT],
                'activationRate' => 100,
                'modifierConfigs' => [],
                'spawnEquipmentConfigs' => [
                    '1_auxiliary_terminal_in_medlab',
                    '1_auxiliary_terminal_in_engine_room',
                ],
                'replaceEquipmentConfigs' => [],
            ],
            [
                'name' => ProjectName::THALASSO,
                'type' => ProjectType::NERON_PROJECT,
                'efficiency' => 18,
                'bonusSkills' => [],
                'activationRate' => 100,
                'modifierConfigs' => [],
                'spawnEquipmentConfigs' => [],
                'replaceEquipmentConfigs' => [
                    'replace_all_showers_by_thalasso',
                ],
            ],
            [
                'name' => ProjectName::BRIC_BROC,
                'type' => ProjectType::NERON_PROJECT,
                'efficiency' => 6,
                'bonusSkills' => [SkillEnum::CONCEPTOR, SkillEnum::CREATIVE],
                'activationRate' => 15,
                'modifierConfigs' => [],
                'spawnEquipmentConfigs' => [],
                'replaceEquipmentConfigs' => [],
            ],
            [
                'name' => ProjectName::AUTO_WATERING,
                'type' => ProjectType::NERON_PROJECT,
                'efficiency' => 3,
                'bonusSkills' => [SkillEnum::TECHNICIAN, SkillEnum::FIREFIGHTER],
                'activationRate' => 25,
                'modifierConfigs' => [],
                'spawnEquipmentConfigs' => [],
                'replaceEquipmentConfigs' => [],
            ],
            [
                'name' => ProjectName::MAGNETIC_NET,
                'type' => ProjectType::NERON_PROJECT,
                'efficiency' => 6,
                'bonusSkills' => [SkillEnum::PILOT, SkillEnum::PHYSICIST],
                'activationRate' => 100,
                'modifierConfigs' => [],
                'spawnEquipmentConfigs' => [],
                'replaceEquipmentConfigs' => [],
            ],
            [
                'name' => ProjectName::ICARUS_ANTIGRAV_PROPELLER,
                'type' => ProjectType::NERON_PROJECT,
                'efficiency' => 12,
                'bonusSkills' => [SkillEnum::PILOT, SkillEnum::PHYSICIST],
                'activationRate' => 100,
                'modifierConfigs' => [],
                'spawnEquipmentConfigs' => [],
                'replaceEquipmentConfigs' => [],
            ],
            [
                'name' => ProjectName::FISSION_COFFEE_ROASTER,
                'type' => ProjectType::NERON_PROJECT,
                'efficiency' => 6,
                'bonusSkills' => [SkillEnum::CAFFEINE_JUNKIE, SkillEnum::PHYSICIST],
                'activationRate' => 100,
                'modifierConfigs' => [],
                'spawnEquipmentConfigs' => [],
                'replaceEquipmentConfigs' => [],
            ],
            [
                'name' => ProjectName::ARMOUR_CORRIDOR,
                'type' => ProjectType::NERON_PROJECT,
                'efficiency' => 3,
                'bonusSkills' => [SkillEnum::TECHNICIAN, SkillEnum::PHYSICIST],
                'activationRate' => 100,
                'modifierConfigs' => [
                    'modifier_for_daedalus_+1hull_on_change.variable_if_reason_hunter_shot',
                ],
                'spawnEquipmentConfigs' => [],
                'replaceEquipmentConfigs' => [],
            ],
            [
                'name' => ProjectName::CALL_OF_DIRTY,
                'type' => ProjectType::NERON_PROJECT,
                'efficiency' => 18,
                'bonusSkills' => [SkillEnum::TECHNICIAN, SkillEnum::SHOOTER],
                'activationRate' => 100,
                'modifierConfigs' => [],
                'spawnEquipmentConfigs' => [
                    '1_dynarcade_in_alpha_bay_2',
                ],
                'replaceEquipmentConfigs' => [],
            ],
            [
                'name' => ProjectName::EXTRA_DRONE,
                'type' => ProjectType::NERON_PROJECT,
                'efficiency' => 6,
                'bonusSkills' => [SkillEnum::TECHNICIAN, SkillEnum::ROBOTICS_EXPERT],
                'activationRate' => 100,
                'modifierConfigs' => [],
                'spawnEquipmentConfigs' => [],
                'replaceEquipmentConfigs' => [],
            ],
        ];
    }
}
