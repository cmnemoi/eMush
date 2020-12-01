<?php

namespace Mush\Item\Service;

use Mush\Game\CycleHandler\AbstractCycleHandler;
use Mush\Item\Entity\ItemType;

interface ItemCycleHandlerServiceInterface
{
    public function getItemCycleHandler(ItemType $itemType): ?AbstractCycleHandler;
}
