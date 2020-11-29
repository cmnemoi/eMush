<?php

namespace Mush\Item\Service;

use Mush\Game\CycleHandler\AbstractCycleHandler;
use Mush\Item\Entity\ItemType;

class ItemCycleHandlerService implements ItemCycleHandlerServiceInterface
{
    private array $strategies = [];

    public function addStrategy(AbstractCycleHandler $cycleHandler)
    {
        $this->strategies[$cycleHandler->getName()] = $cycleHandler;
    }

    public function getItemCycleHandler(ItemType $itemType): ?AbstractCycleHandler
    {
        if (!isset($this->strategies[$itemType->getType()])) {
            return null;
        }

        return $this->strategies[$itemType->getType()];
    }
}
