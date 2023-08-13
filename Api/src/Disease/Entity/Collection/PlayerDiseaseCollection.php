<?php

namespace Mush\Disease\Entity\Collection;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Disease\Entity\PlayerDisease;
use Mush\Disease\Enum\DiseaseStatusEnum;

/**
 * @template-extends ArrayCollection<int, PlayerDisease>
 */
class PlayerDiseaseCollection extends ArrayCollection
{
    public function getActiveDiseases(): self
    {
        return $this->filter(fn (PlayerDisease $disease) => ($disease->getStatus() === DiseaseStatusEnum::ACTIVE));
    }

    public function getByDiseaseType(string $type): self
    {
        return $this->filter(fn (PlayerDisease $disease) => ($disease->getDiseaseConfig()->getType() === $type));
    }
}
