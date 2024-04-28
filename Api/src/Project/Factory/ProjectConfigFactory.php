<?php

declare(strict_types=1);

namespace Mush\Project\Factory;

use Mush\Project\ConfigData\ProjectConfigData;
use Mush\Project\Entity\ProjectConfig;
use Mush\Project\Enum\ProjectName;
use Mush\Project\Enum\ProjectType;

final class ProjectConfigFactory
{
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

    public static function createNeronProjectConfigByName(ProjectName $name): ProjectConfig
    {
        return new ProjectConfig(...self::getConfigDataFromName($name));
    }

    public static function createPilgredConfig(): ProjectConfig
    {
        return new ProjectConfig(
            ...self::getConfigDataFromName(ProjectName::PILGRED)
        );
    }

    public static function createPlasmaShieldConfig(): ProjectConfig
    {
        return new ProjectConfig(
            ...self::getConfigDataFromName(ProjectName::PLASMA_SHIELD)
        );
    }

    public static function createTrailReducerConfig(): ProjectConfig
    {
        return new ProjectConfig(
            ...self::getConfigDataFromName(ProjectName::TRAIL_REDUCER)
        );
    }

    public static function createHeatLampConfig(): ProjectConfig
    {
        return new ProjectConfig(
            ...self::getConfigDataFromName(ProjectName::HEAT_LAMP)
        );
    }

    private static function getConfigDataFromName(ProjectName $name): array
    {
        return current(array_filter(
            ProjectConfigData::getAll(),
            static fn ($config) => $config['name'] === $name
        ));
    }
}
