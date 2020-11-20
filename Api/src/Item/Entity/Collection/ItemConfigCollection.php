<?php

namespace Mush\Item\Entity\Collection;

use Doctrine\Common\Collections\ArrayCollection;

class ItemConfigCollection extends ArrayCollection
{
    public function getByStatusName(string $statusName): Collection
    {
        return $this->filter(fn (GameItem $gameItem) => $gameItem->getStatusByName($statusName));
    }
}
