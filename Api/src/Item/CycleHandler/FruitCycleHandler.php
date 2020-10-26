<?php


namespace Mush\Item\CycleHandler;

use Mush\Game\CycleHandler\CycleHandlerInterface;
use Mush\Item\Entity\Fruit;
use Mush\Item\Entity\GameItem;

class FruitCycleHandler implements CycleHandlerInterface
{
    public function handleNewCycle($fruit, \DateTime $dateTime)
    {
        if (!$fruit instanceof GameItem || !$fruit->getItem() instanceof Fruit) {
            return;
        }
    }

    public function handleNewDay($fruit, \DateTime $dateTime)
    {
        if (!$fruit instanceof GameItem || !$fruit->getItem() instanceof Fruit) {
            return;
        }
    }
}
