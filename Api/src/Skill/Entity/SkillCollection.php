<?php

declare(strict_types=1);

namespace Mush\Skill\Entity;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @template-extends ArrayCollection<int, Skill>
 */
class SkillCollection extends ArrayCollection
{
    public function getHumanSkills(): self
    {
        return $this->filter(static fn (Skill $skill) => $skill->isHumanSkill());
    }

    public function getMushSkills(): self
    {
        return $this->filter(static fn (Skill $skill) => $skill->isMushSkill());
    }
}
