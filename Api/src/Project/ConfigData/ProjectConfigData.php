<?php

declare(strict_types=1);

namespace Mush\Project\ConfigData;

use Mush\Game\Enum\SkillEnum;
use Mush\Project\Enum\ProjectName;
use Mush\Project\Enum\ProjectType;

/**
 * @codeCoverageIgnore
 */
final class ProjectConfigData
{
    public static function getAll(): array
    {
        return [
            [
                'name' => ProjectName::PILGRED,
                'type' => ProjectType::PILGRED,
                'efficiency' => 1,
                'bonusSkills' => [SkillEnum::PHYSICIST, SkillEnum::TECHNICIAN],
            ],
            [
                'name' => ProjectName::PATROL_SHIP_LAUNCHER,
                'type' => ProjectType::NERON_PROJECT,
                'efficiency' => 6,
                'bonusSkills' => [SkillEnum::PILOT, SkillEnum::PHYSICIST],
            ],
            [
                'name' => ProjectName::PATROL_SHIP_BLASTER_GUN,
                'type' => ProjectType::NERON_PROJECT,
                'efficiency' => 6,
                'bonusSkills' => [SkillEnum::PILOT, SkillEnum::SHOOTER],
            ],
            [
                'name' => ProjectName::BAY_DOOR_XXL,
                'type' => ProjectType::NERON_PROJECT,
                'efficiency' => 6,
                'bonusSkills' => [SkillEnum::PILOT, SkillEnum::TECHNICIAN],
            ],
            [
                'name' => ProjectName::PATROL_SHIP_EXTRA_AMMO,
                'type' => ProjectType::NERON_PROJECT,
                'efficiency' => 6,
                'bonusSkills' => [SkillEnum::PHYSICIST, SkillEnum::TECHNICIAN],
            ],
            [
                'name' => ProjectName::PATROL_SHIP_EXTRA_AMMO,
                'type' => ProjectType::NERON_PROJECT,
                'efficiency' => 6,
                'bonusSkills' => [SkillEnum::PHYSICIST, SkillEnum::TECHNICIAN],
            ],
            [
                'name' => ProjectName::TURRET_EXTRA_FIRE_RATE,
                'type' => ProjectType::NERON_PROJECT,
                'efficiency' => 12,
                'bonusSkills' => [SkillEnum::PHYSICIST, SkillEnum::TECHNICIAN],
            ],
            [
                'name' => ProjectName::FIRE_SENSOR,
                'type' => ProjectType::NERON_PROJECT,
                'efficiency' => 18,
                'bonusSkills' => [SkillEnum::RADIO_EXPERT, SkillEnum::FIREFIGHTER],
            ],
            [
                'name' => ProjectName::DOOR_SENSOR,
                'type' => ProjectType::NERON_PROJECT,
                'efficiency' => 18,
                'bonusSkills' => [SkillEnum::RADIO_EXPERT, SkillEnum::ROBOTICS_EXPERT],
            ],
            [
                'name' => ProjectName::EQUIPMENT_SENSOR,
                'type' => ProjectType::NERON_PROJECT,
                'efficiency' => 18,
                'bonusSkills' => [SkillEnum::RADIO_EXPERT, SkillEnum::TECHNICIAN],
            ],
            [
                'name' => ProjectName::QUANTUM_SENSORS,
                'type' => ProjectType::NERON_PROJECT,
                'efficiency' => 6,
                'bonusSkills' => [SkillEnum::ASTROPHYSICIST, SkillEnum::RADIO_EXPERT],
            ],
            [
                'name' => ProjectName::CHIPSET_ACCELERATION,
                'type' => ProjectType::NERON_PROJECT,
                'efficiency' => 6,
                'bonusSkills' => [SkillEnum::ASTROPHYSICIST, SkillEnum::IT_EXPERT],
            ],
            [
                'name' => ProjectName::ICARUS_ANTIGRAV_PROPELLER,
                'type' => ProjectType::NERON_PROJECT,
                'efficiency' => 12,
                'bonusSkills' => [SkillEnum::PILOT, SkillEnum::PHYSICIST],
            ],
            [
                'name' => ProjectName::ICARUS_LARGER_BAY,
                'type' => ProjectType::NERON_PROJECT,
                'efficiency' => 6,
                'bonusSkills' => [SkillEnum::PILOT, SkillEnum::TECHNICIAN],
            ],
            [
                'name' => ProjectName::ICARUS_LAVATORY,
                'type' => ProjectType::NERON_PROJECT,
                'efficiency' => 18,
                'bonusSkills' => [SkillEnum::BIOLOGIST, SkillEnum::TECHNICIAN],
            ],
            [
                'name' => ProjectName::NERON_PROJECT_THREAD,
                'type' => ProjectType::NERON_PROJECT,
                'efficiency' => 6,
                'bonusSkills' => [SkillEnum::IT_EXPERT, SkillEnum::SHRINK],
            ],
            [
                'name' => ProjectName::ARMOUR_CORRIDOR,
                'type' => ProjectType::NERON_PROJECT,
                'efficiency' => 6,
                'bonusSkills' => [SkillEnum::TECHNICIAN, SkillEnum::PHYSICIST],
            ],
            [
                'name' => ProjectName::PLASMA_SHIELD,
                'type' => ProjectType::NERON_PROJECT,
                'efficiency' => 1,
                'bonusSkills' => [SkillEnum::TECHNICIAN, SkillEnum::PHYSICIST],
            ],
            [
                'name' => ProjectName::EXTRA_HYDROPONPOTS,
                'type' => ProjectType::NERON_PROJECT,
                'efficiency' => 6,
                'bonusSkills' => [SkillEnum::TECHNICIAN, SkillEnum::BOTANIST],
            ],
            [
                'name' => ProjectName::HYDROPONIC_INCUBATOR,
                'type' => ProjectType::NERON_PROJECT,
                'efficiency' => 6,
                'bonusSkills' => [SkillEnum::PHYSICIST, SkillEnum::BOTANIST],
            ],
            [
                'name' => ProjectName::HEAT_LAMP,
                'type' => ProjectType::NERON_PROJECT,
                'efficiency' => 3,
                'bonusSkills' => [SkillEnum::TECHNICIAN, SkillEnum::BOTANIST],
            ],
            [
                'name' => ProjectName::AUXILIARY_TERMINAL,
                'type' => ProjectType::NERON_PROJECT,
                'efficiency' => 6,
                'bonusSkills' => [SkillEnum::IT_EXPERT, SkillEnum::RADIO_EXPERT],
            ],
            [
                'name' => ProjectName::AUTO_WATERING,
                'type' => ProjectType::NERON_PROJECT,
                'efficiency' => 3,
                'bonusSkills' => [SkillEnum::TECHNICIAN, SkillEnum::FIREFIGHTER],
            ],
            [
                'name' => ProjectName::RADAR_TRANS_VOID,
                'type' => ProjectType::NERON_PROJECT,
                'efficiency' => 12,
                'bonusSkills' => [SkillEnum::TECHNICIAN, SkillEnum::RADIO_EXPERT],
            ],
            [
                'name' => ProjectName::MAGNETIC_NET,
                'type' => ProjectType::NERON_PROJECT,
                'efficiency' => 6,
                'bonusSkills' => [SkillEnum::PILOT, SkillEnum::PHYSICIST],
            ],
            [
                'name' => ProjectName::FOOD_RETAILER,
                'type' => ProjectType::NERON_PROJECT,
                'efficiency' => 6,
                'bonusSkills' => [SkillEnum::ROBOTICS_EXPERT, SkillEnum::CHEF],
            ],
            [
                'name' => ProjectName::DISMANTLING,
                'type' => ProjectType::NERON_PROJECT,
                'efficiency' => 18,
                'bonusSkills' => [SkillEnum::TECHNICIAN, SkillEnum::ROBOTICS_EXPERT],
            ],
            [
                'name' => ProjectName::AUTO_RETURN_ICARUS,
                'type' => ProjectType::NERON_PROJECT,
                'efficiency' => 18,
                'bonusSkills' => [SkillEnum::TECHNICIAN, SkillEnum::ROBOTICS_EXPERT],
            ],
            [
                'name' => ProjectName::EXTRA_DRONE,
                'type' => ProjectType::NERON_PROJECT,
                'efficiency' => 6,
                'bonusSkills' => [SkillEnum::TECHNICIAN, SkillEnum::ROBOTICS_EXPERT],
            ],
            [
                'name' => ProjectName::FISSION_COFFEE_ROASTER,
                'type' => ProjectType::NERON_PROJECT,
                'efficiency' => 6,
                'bonusSkills' => [SkillEnum::CAFFEINE_JUNKIE, SkillEnum::PHYSICIST],
            ],
            [
                'name' => ProjectName::TRAIL_REDUCER,
                'type' => ProjectType::NERON_PROJECT,
                'efficiency' => 6,
                'bonusSkills' => [SkillEnum::PILOT, SkillEnum::TECHNICIAN],
            ],
            [
                'name' => ProjectName::OXY_MORE,
                'type' => ProjectType::NERON_PROJECT,
                'efficiency' => 6,
                'bonusSkills' => [SkillEnum::TECHNICIAN, SkillEnum::BIOLOGIST],
            ],
            [
                'name' => ProjectName::BRIC_BROC,
                'type' => ProjectType::NERON_PROJECT,
                'efficiency' => 6,
                'bonusSkills' => [SkillEnum::DESIGNER, SkillEnum::CREATIVE],
            ],
            [
                'name' => ProjectName::TRASH_LOAD,
                'type' => ProjectType::NERON_PROJECT,
                'efficiency' => 18,
                'bonusSkills' => [SkillEnum::TECHNICIAN, SkillEnum::ROBOTICS_EXPERT],
            ],
            [
                'name' => ProjectName::WHOS_WHO,
                'type' => ProjectType::NERON_PROJECT,
                'efficiency' => 18,
                'bonusSkills' => [SkillEnum::RADIO_EXPERT, SkillEnum::PARANOID],
            ],
            [
                'name' => ProjectName::CALL_OF_DIRTY,
                'type' => ProjectType::NERON_PROJECT,
                'efficiency' => 18,
                'bonusSkills' => [SkillEnum::TECHNICIAN, SkillEnum::SHOOTER],
            ],
            [
                'name' => ProjectName::BEAT_BOX,
                'type' => ProjectType::NERON_PROJECT,
                'efficiency' => 18,
                'bonusSkills' => [SkillEnum::TECHNICIAN, SkillEnum::CREATIVE],
            ],
            [
                'name' => ProjectName::THALASSO,
                'type' => ProjectType::NERON_PROJECT,
                'efficiency' => 18,
                'bonusSkills' => [SkillEnum::TECHNICIAN, SkillEnum::NURSE],
            ],
            [
                'name' => ProjectName::FLOOR_HEATING,
                'type' => ProjectType::NERON_PROJECT,
                'efficiency' => 18,
                'bonusSkills' => [SkillEnum::TECHNICIAN, SkillEnum::NURSE],
            ],
            [
                'name' => ProjectName::NOISE_REDUCER,
                'type' => ProjectType::NERON_PROJECT,
                'efficiency' => 18,
                'bonusSkills' => [SkillEnum::TECHNICIAN, SkillEnum::PHYSICIST],
            ],
            [
                'name' => ProjectName::NERON_TARGETING_ASSIST,
                'type' => ProjectType::NERON_PROJECT,
                'efficiency' => 3,
                'bonusSkills' => [SkillEnum::PHYSICIST, SkillEnum::PILOT],
            ],
            [
                'name' => ProjectName::PARASITE_ELM,
                'type' => ProjectType::NERON_PROJECT,
                'efficiency' => 18,
                'bonusSkills' => [SkillEnum::BOTANIST, SkillEnum::BIOLOGIST],
            ],
            [
                'name' => ProjectName::APERO_KITCHEN,
                'type' => ProjectType::NERON_PROJECT,
                'efficiency' => 18,
                'bonusSkills' => [SkillEnum::CHEF, SkillEnum::LOGISTICS_EXPERT],
            ],
        ];
    }
}
