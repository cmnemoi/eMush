<?php

declare(strict_types=1);

namespace Mush\Skill\Repository;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Skill\Entity\SkillConfig;
use Mush\Skill\Enum\SkillEnum;

interface SkillConfigRepositoryInterface
{
    public function findOneByNameAndDaedalusOrThrow(SkillEnum $skill, Daedalus $daedalus): SkillConfig;
}
