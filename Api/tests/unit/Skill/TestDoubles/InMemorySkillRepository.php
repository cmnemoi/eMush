<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Skill\TestDoubles;

use Mush\Skill\Entity\Skill;
use Mush\Skill\Repository\SkillRepositoryInterface;

final class InMemorySkillRepository implements SkillRepositoryInterface
{
    private array $skills = [];

    public function delete(Skill $skill): void
    {
        $player = $skill->getPlayer();
        $player->removeSkill($skill);

        unset($this->skills[$skill->getName()->toString()]);
    }

    public function save(Skill $skill): void
    {
        $this->skills[$skill->getName()->toString()] = $skill;
    }

    public function clear(): void
    {
        $this->skills = [];
    }
}
