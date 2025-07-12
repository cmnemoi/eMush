<?php

declare(strict_types=1);

namespace Mush\Project\Factory;

use Mush\Project\ConfigData\ProjectConfigData;
use Mush\Project\ConfigData\ProjectRequirementsConfigData;
use Mush\Project\Entity\ProjectConfig;
use Mush\Project\Entity\ProjectRequirement;
use Mush\Project\Enum\ProjectName;
use Mush\Project\Enum\ProjectRequirementName;
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

    public static function createProjectConfigByName(ProjectName $name): ProjectConfig
    {
        $config = self::getConfigDataFromName($name);

        return new ProjectConfig(
            name: $name,
            type: $config['type'],
            efficiency: $config['efficiency'],
            bonusSkills: $config['bonusSkills'],
            activationRate: $config['activationRate'],
            modifierConfigs: $config['modifierConfigs'],
            spawnEquipmentConfigs: $config['spawnEquipmentConfigs'],
            replaceEquipmentConfigs: $config['replaceEquipmentConfigs'],
            requirements: array_map(static fn (ProjectRequirementName $requirementName) => ProjectRequirement::fromDto(ProjectRequirementsConfigData::getByName($requirementName)), $config['requirements']),
        );
    }

    public static function createNullConfig(): ProjectConfig
    {
        return new ProjectConfig(
            name: ProjectName::NULL,
            type: ProjectType::NULL,
            efficiency: 0,
            bonusSkills: []
        );
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

    public static function createCpuOverclockingConfig(): ProjectConfig
    {
        return new ProjectConfig(
            ...self::getConfigDataFromName(ProjectName::CHIPSET_ACCELERATION)
        );
    }

    private static function getConfigDataFromName(ProjectName $name): array
    {
        $data = current(array_filter(
            ProjectConfigData::getAll(),
            static fn ($config) => $config['name'] === $name
        ));
        if (!$data) {
            throw new \Exception("Project config {$name->toString()} not found");
        }

        return $data;
    }
}
