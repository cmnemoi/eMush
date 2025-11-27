<?php

declare(strict_types=1);

namespace Mush\Skill\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Order;
use Mush\Skill\Enum\SkillEnum;

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

    public function getSkillsWithPoints(): self
    {
        return $this->filter(static fn (Skill $skill) => $skill->hasSkillPoints());
    }

    public function addSkills(self $skills): self
    {
        return new self(array_merge($this->toArray(), $skills->toArray()));
    }

    public function getSortedBy(string $criteria, Order $order = Order::Ascending): self
    {
        $criteria = Criteria::create()->orderBy([$criteria => $order]);

        return new self($this->matching($criteria)->toArray());
    }

    public function getAllExcept(SkillEnum $skillName): self
    {
        return $this->filter(static fn (Skill $skill) => $skill->getName() !== $skillName);
    }
}
