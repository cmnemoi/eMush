<?php

namespace Mush\Daedalus\Event;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Event\AbstractCycleEvent;

class DaedalusCycleEvent extends AbstractCycleEvent
{
    public const DAEDALUS_NEW_CYCLE = 'daedalus.new.cycle';
    public const DAEDALUS_NEW_DAY = 'daedalus.new.day';

    private Daedalus $daedalus;

    public function __construct(Daedalus $daedalus, \DateTime $time)
    {
        parent::__construct($time);

        $this->daedalus = $daedalus;
    }

    public function getDaedalus(): Daedalus
    {
        return $this->daedalus;
    }
}
