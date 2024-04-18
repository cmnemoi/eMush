<?php

declare(strict_types=1);

namespace Mush\Exploration\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Order;

/**
 * @template-extends ArrayCollection<int, ExplorationLog>
 */
final class ExplorationLogCollection extends ArrayCollection
{
    /**
     * @psalm-suppress MoreSpecificReturnType
     * @psalm-suppress LessSpecificReturnStatement
     */
    public function getLogsSortedBy(string $criteriaName, bool $descending = false): self
    {
        $sortingMode = $descending ? Order::Descending : Order::Ascending;
        $criteria = Criteria::create()->orderBy([$criteriaName => $sortingMode]);

        // @var \Doctrine\Common\Collections\Collection<array-key, self>&\Doctrine\Common\Collections\Selectable<array-key, self> $result
        return $this->matching($criteria);
    }
}
