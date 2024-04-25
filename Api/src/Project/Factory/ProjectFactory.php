<?php

declare(strict_types=1);

namespace Mush\Project\Factory;

use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Project\Entity\Project;

final class ProjectFactory
{
    public static function createPlasmaShieldProject(): Project
    {
        $daedalus = DaedalusFactory::createDaedalus();
        $project = new Project(
            config: ProjectConfigFactory::createPlasmaShieldConfig(),
            daedalus: $daedalus,
        );
        $daedalus->addProject($project);

        return $project;
    }

    public static function createPilgredProject(): Project
    {
        $daedalus = DaedalusFactory::createDaedalus();
        $project = new Project(
            config: ProjectConfigFactory::createPilgredConfig(),
            daedalus: $daedalus,
        );
        $daedalus->addProject($project);

        return $project;
    }

    public static function createTrailReducerProject(): Project
    {
        $daedalus = DaedalusFactory::createDaedalus();
        $project = new Project(
            config: ProjectConfigFactory::createTrailReducerConfig(),
            daedalus: $daedalus,
        );
        $daedalus->addProject($project);

        return $project;
    }

    public static function createAutoWateringProject(): Project
    {
        $daedalus = DaedalusFactory::createDaedalus();
        $project = new Project(
            config: ProjectConfigFactory::createAutoWateringConfig(),
            daedalus: $daedalus,
        );
        $daedalus->addProject($project);

        return $project;
    }
}
