<?php

namespace Mush\Daedalus\Event;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Event\AbstractGameEvent;

class DaedalusCycleEvent extends AbstractGameEvent
{
    public const DAEDALUS_NEW_CYCLE = 'daedalus.new.cycle';
    public const DAEDALUS_NEW_DAY = 'daedalus.new.day';

    protected Daedalus $daedalus;

    public function __construct(Daedalus $daedalus, array $tags, \DateTime $time)
    {
        parent::__construct($tags, $time);

        $this->daedalus = $daedalus;
    }

    public function getDaedalus(): Daedalus
    {
        return $this->daedalus;
    }
}
