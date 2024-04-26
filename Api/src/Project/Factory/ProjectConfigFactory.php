<?php

declare(strict_types=1);

namespace Mush\Project\Factory;

use Mush\Game\Enum\SkillEnum;
use Mush\Project\Entity\ProjectConfig;
use Mush\Project\Enum\ProjectName;
use Mush\Project\Enum\ProjectType;

final class ProjectConfigFactory
{   
    public static function createNeronProjectConfigByName(ProjectName $name): ProjectConfig
    {
        return new ProjectConfig(
            name: $name,
            type: ProjectType::NERON_PROJECT,
            efficiency: 0,
            bonusSkills: []
        );
    }

    public static function createDummyNeronProjectConfig(): ProjectConfig
    {
        return new ProjectConfig(
            name: ProjectName::NULL,
            type: ProjectType::NERON_PROJECT,
            efficiency: 0,
            bonusSkills: []
        );
    }

    public static function createDummyResearchConfig(): ProjectConfig
    {
        return new ProjectConfig(
            name: ProjectName::NULL,
            type: ProjectType::RESEARCH,
            efficiency: 0,
            bonusSkills: []
        );
    }

    public static function createPilgredConfig(): ProjectConfig
    {
        return new ProjectConfig(
            name: ProjectName::PILGRED,
            type: ProjectType::NERON_PROJECT,
            efficiency: 1,
            bonusSkills: [SkillEnum::PHYSICIST, SkillEnum::TECHNICIAN]
        );
    }

    public static function createPlasmaShieldConfig(): ProjectConfig
    {
        return new ProjectConfig(
            name: ProjectName::PLASMA_SHIELD,
            type: ProjectType::NERON_PROJECT,
            efficiency: 1,
            bonusSkills: [SkillEnum::PHYSICIST, SkillEnum::TECHNICIAN]
        );
    }

    public static function createTrailReducerConfig(): ProjectConfig
    {
        return new ProjectConfig(
            name: ProjectName::TRAIL_REDUCER,
            type: ProjectType::NERON_PROJECT,
            efficiency: 6,
            bonusSkills: [SkillEnum::PILOT, SkillEnum::TECHNICIAN]
        );
    }

    public static function createAutoWateringConfig(): ProjectConfig
    {
        return new ProjectConfig(
            name: ProjectName::AUTO_WATERING,
            type: ProjectType::NERON_PROJECT,
            efficiency: 3,
            bonusSkills: [SkillEnum::TECHNICIAN, SkillEnum::FIREFIGHTER]
        );
    }
}
