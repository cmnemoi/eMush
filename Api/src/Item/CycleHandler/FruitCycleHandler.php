<?php


namespace Mush\Item\CycleHandler;

use Mush\Game\CycleHandler\CycleHandlerInterface;
use Mush\Item\Entity\Fruit;

class FruitCycleHandler implements CycleHandlerInterface
{
    public function handleNewCycle($fruit, \DateTime $dateTime)
    {
        if (!$fruit instanceof Fruit) {
            return;
        }
    }

    public function handleNewDay($fruit, \DateTime $dateTime)
    {
        if (!$fruit instanceof Fruit) {
            return;
        }
    }
}
