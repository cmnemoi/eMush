<?php

declare(strict_types=1);

namespace Mush\Project\Factory;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Project\Entity\Project;
use Mush\Project\Enum\ProjectName;

final class ProjectFactory
{   
    public static function createNeronProjectByName(ProjectName $name): Project
    {
        return new Project(
            config: ProjectConfigFactory::createNeronProjectConfigByName($name),
            daedalus: DaedalusFactory::createDaedalus(),
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

    public static function createHeatLampProjectWithDaedalus(Daedalus $daedalus): Project
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

    public static function createPlasmaShieldProjectWithDaedalus(Daedalus $daedalus): Project
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
            config: ProjectConfigFactory::createNeronProjectConfigByName($name),
            daedalus: $daedalus,
        );
    }
}
