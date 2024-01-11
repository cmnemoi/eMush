<?php

declare(strict_types=1);

namespace Mush\Exploration\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;

/**
 * @template-extends ArrayCollection<int, ExplorationLog>
 */
final class ExplorationLogCollection extends ArrayCollection
{
    public function getLogsSortedBy(string $criteriaName, bool $descending = false): self
    {
        $sortingMode = $descending ? Criteria::DESC : Criteria::ASC;
        $criteria = Criteria::create()->orderBy([$criteriaName => $sortingMode]);

        /** @var ExplorationLogCollection $result */
        $result = $this->matching($criteria);

        return $result;
    }
}
