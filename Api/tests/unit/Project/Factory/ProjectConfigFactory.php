<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Project\Factory;

use Mush\Game\Enum\SkillEnum;
use Mush\Project\Entity\ProjectConfig;
use Mush\Project\Enum\ProjectName;
use Mush\Project\Enum\ProjectType;

final class ProjectConfigFactory
{
    public static function createPlasmaShieldConfig(): ProjectConfig
    {
        return new ProjectConfig(
            name: ProjectName::PLASMA_SHIELD,
            type: ProjectType::NERON_PROJECT,
            efficiency: 1,
            bonusSkills: [SkillEnum::PHYSICIST, SkillEnum::TECHNICIAN]
        );
    }
}
