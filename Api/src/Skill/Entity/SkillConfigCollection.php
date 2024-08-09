<?php

declare(strict_types=1);

namespace Mush\Skill\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Player\Entity\Player;
use Mush\Skill\Enum\SkillEnum;

/**
 * @template-extends ArrayCollection<int, SkillConfig>
 */
final class SkillConfigCollection extends ArrayCollection
{
    public function doesNotContain(SkillEnum $skill): bool
    {
        return $this->filter(static fn (SkillConfig $skillConfig) => $skillConfig->getName() === $skill)->isEmpty();
    }

    public function getAllExcept(SkillEnum $skill): self
    {
        return $this->filter(static fn (SkillConfig $skillConfig) => $skillConfig->getName() !== $skill);
    }

    public function getAllExceptThoseLearnedByPlayer(Player $player): self
    {
        return $this->filter(static fn (SkillConfig $skillConfig) => $player->hasSkill($skillConfig->getName()) === false);
    }
}
