<?php


namespace Mush\Game\CycleHandler;


interface CycleHandlerInterface
{
    public function handleNewCycle($object, \DateTime $dateTime);

    public function handleNewDay($object, \DateTime $dateTime);
}