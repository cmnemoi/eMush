<?php

namespace Mush\Hunter\Entity;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @template-extends ArrayCollection<int, Hunter>
 */
class HunterCollection extends ArrayCollection
{
    public function getAttackingHunters(): self
    {
        return $this->filter(fn (Hunter $hunter) => (!$hunter->isInPool()));
    }

    public function getHunterPool(): self
    {
        return $this->filter(fn (Hunter $hunter) => $hunter->isInPool());
    }

    public function getAllHuntersByType(string $type): self
    {
        return $this->filter(fn (Hunter $hunter) => ($hunter->getHunterConfig()->getHunterName() === $type));
    }
}
