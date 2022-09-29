<?php

namespace Mush\Equipment\Entity\Collection;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Mush\Equipment\Entity\Item;

class ItemCollection extends ArrayCollection
{
    public function getByStatusName(string $statusName): Collection
    {
        return $this->filter(fn (Item $gameItem) => $gameItem->getStatusByName($statusName));
    }
}
