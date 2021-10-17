<?php

namespace Mush\Equipment\Entity\Config\Collection;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Mush\Equipment\Entity\Config\GameItem;

class ItemCollection extends ArrayCollection
{
    public function getByStatusName(string $statusName): Collection
    {
        return $this->filter(fn (GameItem $gameItem) => $gameItem->getStatusByName($statusName));
    }
}
