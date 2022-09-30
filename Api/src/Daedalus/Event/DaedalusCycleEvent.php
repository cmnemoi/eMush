<?php

namespace Mush\Daedalus\Event;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Game\Event\AbstractModifierHolderEvent;

class DaedalusCycleEvent extends AbstractModifierHolderEvent
{
    public const DAEDALUS_NEW_CYCLE = 'daedalus.new.cycle';
    public const DAEDALUS_NEW_DAY = 'daedalus.new.day';

    protected Daedalus $daedalus;

    public function __construct(Daedalus $daedalus, string $reason, \DateTime $time)
    {
        parent::__construct($daedalus, $reason, $time);

        $this->daedalus = $daedalus;
    }

    public function getDaedalus(): Daedalus
    {
        return $this->daedalus;
    }
}
