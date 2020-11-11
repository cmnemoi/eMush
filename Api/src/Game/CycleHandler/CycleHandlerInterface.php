<?php

namespace Mush\Game\CycleHandler;

use Mush\Daedalus\Entity\Daedalus;

interface CycleHandlerInterface
{
    public function handleNewCycle($object, Daedalus $daedalus, \DateTime $dateTime);

    public function handleNewDay($object, Daedalus $daedalus, \DateTime $dateTime);
}
