<?php

declare(strict_types=1);

namespace Mush\Project\Factory;

use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Project\Entity\Project;

final class ProjectFactory
{
    public static function createPlasmaShieldProject(): Project
    {
        return new Project(
            config: ProjectConfigFactory::createPlasmaShieldConfig(),
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
}
