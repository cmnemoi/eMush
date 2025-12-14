<?php

declare(strict_types=1);

namespace Mush\Skill\Repository;

use Mush\Skill\Entity\Skill;
use Mush\Skill\Enum\SkillEnum;

interface SkillRepositoryInterface
{
    public function delete(Skill $skill): void;

    public function countSkill(SkillEnum $skillName): int;

    public function countSkillByCharacter(string $characterName): array;

    public function countAllSkill(): int;
}
