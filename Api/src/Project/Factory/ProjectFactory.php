<?php

declare(strict_types=1);

namespace Mush\Project\Factory;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Project\Entity\Project;
use Mush\Project\Enum\ProjectName;

final class ProjectFactory
{
    public static function createProjectByName(ProjectName $name): Project
    {
        return new Project(
            config: ProjectConfigFactory::createProjectConfigByName($name),
            daedalus: DaedalusFactory::createDaedalus(),
        );
    }

    public static function createProjectByNameForDaedalus(ProjectName $name, Daedalus $daedalus): Project
    {
        return new Project(
            config: ProjectConfigFactory::createProjectConfigByName($name),
            daedalus: $daedalus,
        );
    }

    public static function createHeatLampProject(): Project
    {
        return new Project(
            config: ProjectConfigFactory::createHeatLampConfig(),
            daedalus: DaedalusFactory::createDaedalus(),
        );
    }

    public static function createPilgredProject(): Project
    {
        return new Project(
            config: ProjectConfigFactory::createPilgredConfig(),
            daedalus: DaedalusFactory::createDaedalus(),
        );
    }

    public static function createPlasmaShieldProject(): Project
    {
        return new Project(
            config: ProjectConfigFactory::createPlasmaShieldConfig(),
            daedalus: DaedalusFactory::createDaedalus(),
        );
    }

    public static function createTrailReducerProject(): Project
    {
        return new Project(
            config: ProjectConfigFactory::createTrailReducerConfig(),
            daedalus: DaedalusFactory::createDaedalus(),
        );
    }

    public static function createCpuPriorityProject(): Project
    {
        return new Project(
            config: ProjectConfigFactory::createCpuOverclockingConfig(),
            daedalus: DaedalusFactory::createDaedalus(),
        );
    }

    public static function createHeatLampProjectForDaedalus(Daedalus $daedalus): Project
    {
        return new Project(
            config: ProjectConfigFactory::createHeatLampConfig(),
            daedalus: $daedalus,
        );
    }

    public static function createDummyNeronProjectForDaedalus(Daedalus $daedalus): Project
    {
        return new Project(
            config: ProjectConfigFactory::createDummyNeronProjectConfig(),
            daedalus: $daedalus,
        );
    }

    public static function createDummyResearchForDaedalus(Daedalus $daedalus): Project
    {
        return new Project(
            config: ProjectConfigFactory::createDummyResearchConfig(),
            daedalus: $daedalus,
        );
    }

    public static function createPilgredProjectForDaedalus(Daedalus $daedalus): Project
    {
        return new Project(
            config: ProjectConfigFactory::createPilgredConfig(),
            daedalus: $daedalus,
        );
    }

    public static function createPlasmaShieldProjectForDaedalus(Daedalus $daedalus): Project
    {
        return new Project(
            config: ProjectConfigFactory::createPlasmaShieldConfig(),
            daedalus: $daedalus,
        );
    }

    public static function createTrailReducerProjectWithDaedalus(Daedalus $daedalus): Project
    {
        return new Project(
            config: ProjectConfigFactory::createTrailReducerConfig(),
            daedalus: $daedalus,
        );
    }

    public static function createNeronProjectByNameForDaedalus(ProjectName $name, Daedalus $daedalus): Project
    {
        return new Project(
            config: ProjectConfigFactory::createProjectConfigByName($name),
            daedalus: $daedalus,
        );
    }

    public static function creatCpuPriorityProjectWithDaedalus(Daedalus $daedalus): Project
    {
        return new Project(
            config: ProjectConfigFactory::createCpuOverclockingConfig(),
            daedalus: $daedalus,
        );
    }

    public static function createNullProject(): Project
    {
        $project = new Project(
            config: ProjectConfigFactory::createNullConfig(),
            daedalus: DaedalusFactory::createDaedalus(),
        );
        self::setId($project, 0);

        return $project;
    }

    private static function setId(Project $project, int $id): void
    {
        (new \ReflectionProperty($project, 'id'))->setValue($project, $id);
    }
}
