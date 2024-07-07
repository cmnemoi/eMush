<?php

namespace Mush\Disease\Entity\Collection;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Order;
use Mush\Disease\Entity\PlayerDisease;
use Mush\Disease\Enum\DiseaseStatusEnum;

/**
 * @template-extends ArrayCollection<int, PlayerDisease>
 */
class PlayerDiseaseCollection extends ArrayCollection
{
    public function getActiveDiseases(): self
    {
        return $this->filter(static fn (PlayerDisease $disease) => ($disease->getStatus() === DiseaseStatusEnum::ACTIVE));
    }

    public function getByDiseaseType(string $type): self
    {
        return $this->filter(static fn (PlayerDisease $disease) => ($disease->getDiseaseConfig()->getType() === $type));
    }

    /**
     * @psalm-suppress MoreSpecificReturnType
     * @psalm-suppress LessSpecificReturnStatement
     */
    public function getSortedByDiseasePoints(Order $order = Order::Ascending): self
    {
        $criteria = Criteria::create()->orderBy(['diseasePoint' => $order]);

        return $this->matching($criteria);
    }
}
