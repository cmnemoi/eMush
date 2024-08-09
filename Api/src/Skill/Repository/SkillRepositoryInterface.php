<?php

declare(strict_types=1);

namespace Mush\Skill\Repository;

use Mush\Skill\Entity\Skill;

interface SkillRepositoryInterface
{
    public function delete(Skill $skill): void;
}
