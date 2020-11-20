<?php

namespace Mush\Item\CycleHandler;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\CycleHandler\CycleHandlerInterface;
use Mush\Item\Entity\GameItem;
use Mush\Item\Entity\Items\Fruit;
use Mush\Item\Enum\ItemEnum;

class FruitCycleHandler implements CycleHandlerInterface
{
    public function handleNewCycle($fruit, Daedalus $daedalus, \DateTime $dateTime)
    {
        if (!$fruit instanceof GameItem) {
            return;
        }
        $fruitType = $fruit->getItem()->getItemType(ItemTypeEnum::FRUIT);
        if (null === $fruitType || !$fruitType instanceof Fruit) {
            return;
        }
    }

    public function handleNewDay($fruit, Daedalus $daedalus, \DateTime $dateTime)
    {
        if (!$fruit instanceof GameItem) {
            return;
        }
        $fruitType = $fruit->getItem()->getItemType(ItemTypeEnum::FRUIT);
        if (null === $fruitType || !$fruitType instanceof Fruit) {
            return;
        }
    }
}
